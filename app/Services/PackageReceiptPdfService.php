<?php

namespace App\Services;

use App\Helpers\CurrencyHelper;
use App\Models\ContentPackage;
use App\Models\Course;
use App\Models\Order;
use App\Models\Setting;
use App\Models\User;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\View;

class PackageReceiptPdfService
{
    /**
     * Placeholders pour receipt_pack_title / receipt_pack_body (réglages admin).
     *
     * @var array<string, string>
     */
    public const PLACEHOLDERS = [
        'user_name' => 'Nom de l\'utilisateur',
        'package_title' => 'Titre du pack',
        'package_subtitle' => 'Sous-titre du pack',
        'enrollment_date' => 'Date d\'accès / d\'achat',
        'purchase_date' => 'Identique à enrollment_date',
        'order_number' => 'Numéro de commande (référence)',
        'order_id' => 'ID interne de la commande',
        'amount_paid' => 'Prix forfait du pack payé sur la commande (pas la somme des contenus à l’unité)',
        'currency' => 'Code devise',
        'contents_list_html' => 'Liste HTML des titres de contenus inclus',
        'contents_count' => 'Nombre de contenus dans le pack',
        'pack_url' => 'URL de la page « mon pack »',
        'site_name' => 'Nom du site',
    ];

    public const DEFAULT_TITLE = 'Reçu d\'achat — Pack {package_title}';

    public const DEFAULT_BODY = <<<'HTML'
<p>Bonjour <strong>{user_name}</strong>,</p>
<p>Ce document atteste de votre accès au pack <strong>{package_title}</strong> sur {site_name} ({contents_count} contenu(s)).</p>
<p><strong>Contenus inclus :</strong></p>
{contents_list_html}
<p>Accédez au pack et inscrivez-vous aux formations : <a href="{pack_url}">{pack_url}</a></p>
<p>Merci pour votre confiance.</p>
HTML;

    /**
     * Génère le PDF du reçu d'achat / d'accès au pack (binaire).
     */
    public function generatePdfContent(User $user, ContentPackage $package, ?Order $order = null): string
    {
        $package->loadMissing(['contents.provider', 'contents.category']);

        $title = Setting::get('receipt_pack_title', self::DEFAULT_TITLE) ?: self::DEFAULT_TITLE;
        $body = Setting::get('receipt_pack_body', self::DEFAULT_BODY) ?: self::DEFAULT_BODY;

        $replacements = $this->buildReplacements($user, $package, $order);
        $title = $this->replacePlaceholders($title, $replacements);
        $body = $this->replacePlaceholders($body, $replacements);
        $body = html_entity_decode($body, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        $purchaseAt = $this->resolvePurchaseDate($order);
        [$amountNumeric, $amountFormatted] = $this->resolveAmount($package, $order);

        $html = View::make('pdf.package-enrollment-receipt', [
            'title' => $title,
            'body' => $body,
            'user' => $user,
            'package' => $package,
            'courses' => $package->contents,
            'purchaseDate' => $purchaseAt->format('d/m/Y à H:i'),
            'amountFormatted' => $amountFormatted,
            'amountNumeric' => $amountNumeric,
            'order' => $order,
            'orderNumber' => $order?->order_number,
            'logoBase64' => $this->getLogoBase64(),
        ])->render();

        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', false);
        $options->set('defaultFont', 'DejaVu Sans');
        $options->set('chroot', [public_path(), resource_path('views')]);

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        return $dompdf->output();
    }

    /**
     * @return array<string, string>
     */
    private function buildReplacements(User $user, ContentPackage $package, ?Order $order): array
    {
        $purchaseAt = $this->resolvePurchaseDate($order);
        [, $amountFormatted] = $this->resolveAmount($package, $order);
        $currency = $order?->currency ?? config('app.currency', 'XOF');

        $lines = $package->contents->map(function (Course $c) {
            return '<li>' . e($c->title) . '</li>';
        })->implode('');

        $contentsListHtml = $lines !== '' ? '<ul>' . $lines . '</ul>' : '<p>—</p>';

        return [
            'user_name' => $user->name ?? $user->email ?? '—',
            'package_title' => $package->title,
            'package_subtitle' => $package->subtitle ?? '—',
            'enrollment_date' => $purchaseAt->format('d/m/Y à H:i'),
            'purchase_date' => $purchaseAt->format('d/m/Y à H:i'),
            'order_number' => $order?->order_number ? (string) $order->order_number : '—',
            'order_id' => $order ? (string) $order->id : '—',
            'amount_paid' => $amountFormatted,
            'currency' => strtoupper((string) $currency),
            'contents_list_html' => $contentsListHtml,
            'contents_count' => (string) $package->contents->count(),
            'pack_url' => route('customer.pack', $package),
            'site_name' => config('app.name', 'Herime Académie'),
        ];
    }

    private function resolvePurchaseDate(?Order $order): Carbon
    {
        if ($order) {
            if ($order->paid_at) {
                return $order->paid_at instanceof Carbon ? $order->paid_at : Carbon::parse($order->paid_at);
            }
            if ($order->confirmed_at) {
                return $order->confirmed_at instanceof Carbon ? $order->confirmed_at : Carbon::parse($order->confirmed_at);
            }
            if ($order->created_at) {
                return $order->created_at instanceof Carbon ? $order->created_at : Carbon::parse($order->created_at);
            }
        }

        return now();
    }

    /**
     * @return array{0: float|null, 1: string}
     */
    private function resolveAmount(ContentPackage $package, ?Order $order): array
    {
        if (! $order) {
            return [0.0, 'Gratuit (offert)'];
        }

        $order->loadMissing('orderItems');
        $items = $order->orderItems->where('content_package_id', $package->id);
        if ($items->isEmpty()) {
            return [null, '—'];
        }

        // Prix forfait du pack sur la commande (somme des `total` des lignes du pack), pas la somme des prix catalogue des cours.
        $sum = Order::billedAmountForContentPackage($order->orderItems, (int) $package->id);

        if ($sum <= 0) {
            return [0.0, 'Gratuit (offert)'];
        }

        return [$sum, CurrencyHelper::formatWithSymbol($sum, $order->currency)];
    }

    /**
     * @param  array<string, string>  $replacements
     */
    private function replacePlaceholders(string $text, array $replacements): string
    {
        foreach ($replacements as $key => $value) {
            $text = str_replace('{' . $key . '}', $value, $text);
        }

        return $text;
    }

    private function getLogoBase64(): string
    {
        $logoPath = public_path('images/logo-herime-academie.png');
        if (! file_exists($logoPath)) {
            $logoPath = public_path('images/logo-herime-academie-blanc.png');
        }
        if (! file_exists($logoPath)) {
            return '';
        }

        return 'data:image/png;base64,' . base64_encode((string) file_get_contents($logoPath));
    }
}
