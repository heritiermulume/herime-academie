<?php

namespace App\Services;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Setting;
use App\Models\User;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Support\Facades\View;

class EnrollmentReceiptPdfService
{
    /**
     * Placeholders disponibles pour le titre et le corps du reçu.
     * Utilisation : {user_name}, {course_title}, {enrollment_date}, etc.
     */
    public const PLACEHOLDERS = [
        'user_name' => 'Nom complet de l\'utilisateur',
        'course_title' => 'Titre du contenu',
        'enrollment_date' => 'Date d\'inscription',
        'content_type' => 'Type (cours ou contenu téléchargeable)',
        'course_url' => 'URL de la page du cours',
        'site_name' => 'Nom du site',
        'provider_name' => 'Nom du prestataire',
        'category_name' => 'Nom de la catégorie',
        'order_id' => 'Numéro de commande (si achat)',
    ];

    /**
     * Texte de titre par défaut du reçu.
     */
    public const DEFAULT_TITLE = 'Reçu d\'inscription - {course_title}';

    /**
     * Texte de corps par défaut du reçu (HTML autorisé, liens cliquables avec <a href="...">).
     */
    public const DEFAULT_BODY = <<<'HTML'
<p>Bonjour <strong>{user_name}</strong>,</p>
<p>Ce document confirme votre inscription au contenu <strong>{course_title}</strong> sur {site_name}.</p>
<p><strong>Détails :</strong></p>
<ul>
    <li>Date d'inscription : {enrollment_date}</li>
    <li>Type : {content_type}</li>
    <li>Prestataire : {provider_name}</li>
</ul>
<p>Accédez à votre contenu : <a href="{course_url}">{course_url}</a></p>
<p>Merci pour votre confiance.</p>
HTML;

    /**
     * Génère le contenu PDF du reçu d'inscription (binaire).
     *
     * @param  Enrollment  $enrollment  Inscription (avec user et course chargés)
     * @return string Contenu binaire du PDF
     */
    public function generatePdfContent(Enrollment $enrollment): string
    {
        $enrollment->loadMissing(['user', 'course.provider', 'course.category']);

        $user = $enrollment->user;
        $course = $enrollment->course;

        $title = $this->resolveTitle($course);
        $body = $this->resolveBody($course);
        $replacements = $this->getReplacements($enrollment, $user, $course);

        $title = $this->replacePlaceholders($title, $replacements);
        $body = $this->replacePlaceholders($body, $replacements);
        // Décoder les entités HTML au cas où le corps a été copié-collé avec &lt; &gt; etc., pour que le PDF affiche le rendu HTML et non le code
        $body = html_entity_decode($body, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        $logoBase64 = $this->getLogoBase64();

        $html = View::make('pdf.enrollment-receipt', [
            'title' => $title,
            'body' => $body,
            'user' => $user,
            'course' => $course,
            'enrollment' => $enrollment,
            'enrollmentDate' => $enrollment->created_at?->format('d/m/Y à H:i'),
            'logoBase64' => $logoBase64,
        ])->render();

        $options = new Options;
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
     * Titre : personnalisé du contenu ou défaut global ou constante.
     */
    private function resolveTitle(Course $course): string
    {
        if ($course->receipt_custom_title !== null && trim($course->receipt_custom_title) !== '') {
            return trim($course->receipt_custom_title);
        }

        return Setting::get('receipt_default_title', self::DEFAULT_TITLE) ?: self::DEFAULT_TITLE;
    }

    /**
     * Corps : personnalisé du contenu ou défaut global ou constante.
     */
    private function resolveBody(Course $course): string
    {
        if ($course->receipt_custom_body !== null && trim($course->receipt_custom_body) !== '') {
            return trim($course->receipt_custom_body);
        }

        return Setting::get('receipt_default_body', self::DEFAULT_BODY) ?: self::DEFAULT_BODY;
    }

    private function getReplacements(Enrollment $enrollment, User $user, Course $course): array
    {
        $course->loadMissing(['provider', 'category']);

        $contentType = $course->getContentTypeLabel();
        $courseUrl = ($course->is_downloadable || ($course->is_in_person_program ?? false) || $course->isEnrollmentReceiptOnly())
            ? route('contents.show', $course->slug)
            : route('learning.course', $course->slug);

        return [
            'user_name' => $user->name ?? $user->email ?? '—',
            'course_title' => $course->title,
            'enrollment_date' => $enrollment->created_at?->format('d/m/Y à H:i') ?? now()->format('d/m/Y à H:i'),
            'content_type' => $contentType,
            'course_url' => $courseUrl,
            'site_name' => config('app.name', 'Herime Académie'),
            'provider_name' => $course->provider?->name ?? '—',
            'category_name' => $course->category?->name ?? '—',
            'order_id' => $enrollment->order_id ? (string) $enrollment->order_id : '—',
        ];
    }

    private function replacePlaceholders(string $text, array $replacements): string
    {
        foreach ($replacements as $key => $value) {
            $text = str_replace('{'.$key.'}', $value, $text);
        }

        return $text;
    }

    /**
     * Logo Herime Académie en base64 pour l'embed dans le PDF (charte graphique).
     */
    private function getLogoBase64(): string
    {
        $logoPath = public_path('images/logo-herime-academie.png');
        if (! file_exists($logoPath)) {
            $logoPath = public_path('images/logo-herime-academie-blanc.png');
        }
        if (! file_exists($logoPath)) {
            return '';
        }
        $data = file_get_contents($logoPath);

        return 'data:image/png;base64,'.base64_encode($data);
    }
}
