<?php

namespace App\Services;

use App\Mail\PackageEnrolledMail;
use App\Mail\PackageEnrollmentReceiptMail;
use App\Models\ContentPackage;
use App\Models\Order;
use App\Models\SentEmail;
use App\Models\Setting;
use App\Models\User;
use App\Notifications\PackageEnrolled;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;

class PackageEnrollmentNotifier
{
    /**
     * Un email + une notification base de données par commande et par pack (idempotent),
     * plus un reçu PDF unique par pack (si activé globalement).
     */
    public function notify(User $user, ContentPackage $package, ?Order $order = null): void
    {
        if ($order && $this->hasPackageNotificationForOrder($user, $package, $order)) {
            $this->sendPackEnrollmentReceiptIfApplicable($user, $package, $order);

            return;
        }

        $emailAlreadySent = $this->wasPackageEnrollmentEmailSent($user, $package, $order);

        if (! $emailAlreadySent && $user->email && filter_var($user->email, FILTER_VALIDATE_EMAIL)) {
            try {
                $communicationService = app(CommunicationService::class);
            } catch (\Throwable $e) {
                $communicationService = null;
                Log::warning('PackageEnrollmentNotifier: CommunicationService indisponible', [
                    'error' => $e->getMessage(),
                ]);
            }

            $mailable = new PackageEnrolledMail($package, $order);

            try {
                if ($communicationService) {
                    $communicationService->sendEmailAndWhatsApp($user, $mailable);
                } else {
                    Mail::to($user->email)->send($mailable);
                }
            } catch (\Throwable $e) {
                Log::error('PackageEnrollmentNotifier: échec envoi email pack', [
                    'user_id' => $user->id,
                    'package_id' => $package->id,
                    'order_id' => $order?->id,
                    'error' => $e->getMessage(),
                ]);
            }
        } elseif (! $emailAlreadySent) {
            Log::warning('PackageEnrollmentNotifier: email utilisateur invalide ou absent (notification pack quand même tentée)', [
                'user_id' => $user->id,
                'package_id' => $package->id,
                'order_id' => $order?->id,
            ]);
        }

        $this->sendPackEnrollmentReceiptIfApplicable($user, $package, $order);

        try {
            Notification::sendNow($user, new PackageEnrolled($package, $order));
        } catch (\Throwable $e) {
            Log::error('PackageEnrollmentNotifier: échec notification pack', [
                'user_id' => $user->id,
                'package_id' => $package->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    protected function hasPackageNotificationForOrder(User $user, ContentPackage $package, Order $order): bool
    {
        return $user->notifications()
            ->where('type', PackageEnrolled::class)
            ->where('data->order_id', $order->id)
            ->where('data->package_id', $package->id)
            ->exists();
    }

    protected function wasPackageEnrollmentEmailSent(User $user, ContentPackage $package, ?Order $order): bool
    {
        if (! $user->email) {
            return false;
        }

        $q = SentEmail::query()
            ->where('recipient_email', $user->email)
            ->where('metadata->mail_class', PackageEnrolledMail::class)
            ->where('metadata->content_package_id', $package->id)
            ->where('status', 'sent');

        if ($order) {
            $q->where('metadata->order_id', $order->id);
        }

        return $q->exists();
    }

    /**
     * Reçu PDF pack : une fois par commande et par pack (dédoublonnage sent_emails), si receipt_pdf_enabled.
     */
    protected function sendPackEnrollmentReceiptIfApplicable(User $user, ContentPackage $package, ?Order $order): void
    {
        if (! Setting::get('receipt_pdf_enabled', true)) {
            return;
        }

        if (! $user->email || ! filter_var($user->email, FILTER_VALIDATE_EMAIL)) {
            Log::info('Reçu PDF pack non envoyé: email utilisateur invalide ou vide', [
                'user_id' => $user->id,
                'package_id' => $package->id,
                'order_id' => $order?->id,
            ]);

            return;
        }

        if ($this->wasPackReceiptEmailSent($user, $package, $order)) {
            return;
        }

        try {
            $pdfService = app(PackageReceiptPdfService::class);
            $pdfContent = $pdfService->generatePdfContent($user, $package, $order);
            if ($pdfContent === '' || strlen($pdfContent) < 100) {
                throw new \RuntimeException('PDF pack vide ou invalide (taille: ' . strlen($pdfContent) . ' octets).');
            }

            $receiptMail = new PackageEnrollmentReceiptMail($package, $pdfContent, $order);

            try {
                $communicationService = app(CommunicationService::class);
            } catch (\Throwable $e) {
                $communicationService = null;
            }

            if ($communicationService) {
                $communicationService->sendEmailAndWhatsApp($user, $receiptMail, null, false);
            } else {
                Mail::to($user->email)->send($receiptMail);
            }

            Log::info('Reçu PDF pack envoyé', [
                'user_id' => $user->id,
                'package_id' => $package->id,
                'order_id' => $order?->id,
            ]);
        } catch (\Throwable $e) {
            Log::error('Échec génération ou envoi reçu PDF pack', [
                'user_id' => $user->id,
                'package_id' => $package->id,
                'order_id' => $order?->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    protected function wasPackReceiptEmailSent(User $user, ContentPackage $package, ?Order $order): bool
    {
        $q = SentEmail::query()
            ->where('recipient_email', $user->email)
            ->where('metadata->mail_class', PackageEnrollmentReceiptMail::class)
            ->where('metadata->content_package_id', $package->id)
            ->where('status', 'sent');

        if ($order) {
            $q->where('metadata->order_id', $order->id);
        }

        return $q->exists();
    }
}
