<?php

namespace App\Services;

use App\Models\Certificate;
use App\Models\Course;
use App\Models\User;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CertificateService
{
    /**
     * Générer un certificat pour un utilisateur ayant complété un cours
     */
    public function generateCertificate(User $user, Course $course): Certificate
    {
        // Vérifier si un certificat existe déjà pour cet utilisateur et ce cours
        $existingCertificate = Certificate::where('user_id', $user->id)
            ->where('content_id', $course->id)
            ->first();

        if ($existingCertificate) {
            return $existingCertificate;
        }

        // Générer un numéro de certificat unique
        $certificateNumber = $this->generateCertificateNumber($user, $course);

        // Générer le PDF du certificat
        $pdfPath = $this->generatePdf($user, $course, $certificateNumber);

        // Créer l'enregistrement du certificat
        $certificate = Certificate::create([
            'user_id' => $user->id,
            'content_id' => $course->id,
            'certificate_number' => $certificateNumber,
            'title' => $course->title,
            'description' => "Certificat de complétion pour le cours : {$course->title}",
            'file_path' => $pdfPath,
            'issued_at' => now(),
        ]);

        return $certificate;
    }

    /**
     * Générer un numéro de certificat unique
     */
    private function generateCertificateNumber(User $user, Course $course): string
    {
        $prefix = 'HA'; // Herime Académie
        $year = now()->format('Y');
        $userId = str_pad($user->id, 4, '0', STR_PAD_LEFT);
        $contentId = str_pad($course->id, 4, '0', STR_PAD_LEFT);
        $random = strtoupper(Str::random(4));

        $certificateNumber = "{$prefix}-{$year}-{$userId}-{$contentId}-{$random}";

        // Vérifier l'unicité
        while (Certificate::where('certificate_number', $certificateNumber)->exists()) {
            $random = strtoupper(Str::random(4));
            $certificateNumber = "{$prefix}-{$year}-{$userId}-{$contentId}-{$random}";
        }

        return $certificateNumber;
    }

    /**
     * Générer le PDF du certificat avec un design moderne
     */
    private function generatePdf(User $user, Course $course, string $certificateNumber): string
    {
        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);
        $options->set('defaultFont', 'DejaVu Sans');
        $options->set('chroot', public_path());

        $dompdf = new Dompdf($options);

        // Charger le prestataire si nécessaire
        if (!$course->relationLoaded('provider')) {
            $course->load('provider');
        }

        $html = $this->generateCertificateHtml($user, $course, $certificateNumber);

        $dompdf->loadHtml($html);
        // A4 landscape en points: 297mm x 210mm = 842pt x 595pt
        $dompdf->setPaper([0, 0, 842, 595], 'landscape');
        $dompdf->render();

        // Sauvegarder le PDF
        $filename = "certificates/{$user->id}/{$course->id}/" . Str::slug($certificateNumber) . '.pdf';
        
        // Créer le répertoire s'il n'existe pas
        $directory = dirname($filename);
        if (!Storage::disk('public')->exists($directory)) {
            Storage::disk('public')->makeDirectory($directory, 0755, true);
        }
        
        try {
            $pdfOutput = $dompdf->output();
            if (empty($pdfOutput)) {
                throw new \Exception('PDF output is empty');
            }
            $saved = Storage::disk('public')->put($filename, $pdfOutput);
            if (!$saved) {
                throw new \Exception('Failed to save PDF file');
            }
        } catch (\Exception $e) {
            \Log::error('Error saving certificate PDF', [
                'filename' => $filename,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }

        return $filename;
    }

    /**
     * Générer le HTML du certificat avec un design moderne
     */
    private function generateCertificateHtml(User $user, Course $course, string $certificateNumber): string
    {
        $userName = htmlspecialchars($user->name, ENT_QUOTES, 'UTF-8');
        $courseTitle = htmlspecialchars($course->title, ENT_QUOTES, 'UTF-8');
        $providerName = htmlspecialchars($course->provider->name ?? 'Herime Académie', ENT_QUOTES, 'UTF-8');
        $issueDate = now()->format('d/m/Y');
        $certNumber = htmlspecialchars($certificateNumber, ENT_QUOTES, 'UTF-8');
        
        // Chemin du logo - essayer d'abord le logo coloré, puis le blanc
        $logoPath = public_path('images/logo-herime-academie.png');
        if (!file_exists($logoPath)) {
            $logoPath = public_path('images/logo-herime-academie-blanc.png');
        }
        
        $logoBase64 = '';
        if (file_exists($logoPath)) {
            $logoData = file_get_contents($logoPath);
            $logoBase64 = 'data:image/png;base64,' . base64_encode($logoData);
        }
        
        // Construire les parties conditionnelles
        $watermarkHtml = $logoBase64 ? '<div class="watermark"><img src="' . $logoBase64 . '" alt="Herime Académie" /></div>' : '';
        $logoHtml = $logoBase64 ? '<div class="logo-container"><img src="' . $logoBase64 . '" alt="Herime Académie" /></div>' : '<div class="certificate-logo" style="font-size: 36pt; color: #003366; font-weight: bold; margin-bottom: 8mm; letter-spacing: 3pt;">HERIME ACADÉMIE</div>';

        return <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        @page {
            margin: 0;
            size: A4 landscape;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        html {
            width: 297mm;
            height: 210mm;
            margin: 0;
            padding: 0;
        }
        
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            width: 297mm;
            height: 210mm;
            margin: 0;
            padding: 0;
            background: #ffffff;
            position: relative;
            overflow: hidden;
        }
        
        /* Filigrane avec logo */
        .watermark {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            opacity: 0.08;
            z-index: 1;
            pointer-events: none;
            width: 300px;
            height: 300px;
            overflow: hidden;
        }
        
        .watermark img {
            width: 300px;
            height: auto;
        }
        
        .certificate-wrapper {
            width: 297mm;
            height: 210mm;
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            z-index: 2;
            padding: 1.5mm;
        }
        
        .certificate-border {
            border: 4px solid #003366;
            /* Calculer explicitement: 297mm - 3mm (padding) - 1.06mm (bordure) = 292.94mm */
            width: 276mm;
            /* Calculer explicitement: 210mm - 3mm (padding) - 1.06mm (bordure) = 205.94mm */
            height: 188mm;
            position: relative;
            padding: 8mm;
            background: #ffffff;
            box-sizing: border-box;
            overflow: hidden;
        }
        
        /* Décorations de coin */
        .corner-decoration {
            position: absolute;
            width: 50px;
            height: 50px;
            border: 3px solid #ffcc33;
        }
        
        .corner-top-left {
            top: 8mm;
            left: 8mm;
            border-right: none;
            border-bottom: none;
        }
        
        .corner-top-right {
            top: 8mm;
            right: 8mm;
            border-left: none;
            border-bottom: none;
        }
        
        .corner-bottom-left {
            bottom: 8mm;
            left: 8mm;
            border-right: none;
            border-top: none;
        }
        
        .corner-bottom-right {
            bottom: 8mm;
            right: 8mm;
            border-left: none;
            border-top: none;
        }
        
        .certificate-content {
            width: 100%;
            height: 100%;
            position: relative;
        }
        
        .certificate-header {
            text-align: center;
            height: 25mm;
            padding-top: 2mm;
        }
        
        .logo-container {
            margin-bottom: 3mm;
        }
        
        .logo-container img {
            max-height: 60px;
            width: auto;
        }
        
        .certificate-title {
            font-size: 28pt;
            color: #003366;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 2pt;
            line-height: 1.1;
        }
        
        .certificate-body {
            text-align: center;
            height: 120mm;
            padding: 5mm 0;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
        }
        
        .certificate-text {
            font-size: 14pt;
            color: #333;
            line-height: 1.5;
            margin-bottom: 3mm;
        }
        
        .certificate-name {
            font-size: 34pt;
            color: #003366;            
            margin: 4mm 0;
            text-transform: uppercase;
            text-align: center;
            letter-spacing: 1pt;
            border-bottom: 4px solid #ffcc33;
            display: inline-block;
            padding-bottom: 3mm;
            max-width: 90%;
            word-wrap: break-word;
            line-height: 1.1;
        }
        
        .certificate-course {
            font-size: 20pt;
            color: #003366;
            font-weight: 600;
            margin: 4mm 0;
            font-style: italic;
            max-width: 90%;
            word-wrap: break-word;
            text-align: center;
            line-height: 1.2;
        }
        
        .certificate-footer-section {
            height: 45mm;
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
        }
        
        .certificate-footer {
            display: table;
            width: 100%;
            margin-bottom: 3mm;
        }
        
        .certificate-signature {
            text-align: center;
            display: table-cell;
            width: 45%;
            vertical-align: bottom;
        }
        
        .signature-line {
            border-top: 2px solid #003366;
            margin-top: 15px;
            padding-top: 3px;
        }
        
        .signature-name {
            font-size: 12pt;
            color: #003366;
            font-weight: bold;
        }
        
        .signature-title {
            font-size: 10pt;
            color: #666;
            margin-top: 2px;
        }
        
        .certificate-number {
            text-align: center;
            margin-top: 3mm;
            font-size: 9pt;
            color: #999;
        }
        
        .certificate-date {
            font-size: 9pt;
            color: #666;
            margin-top: 2px;
        }
    </style>
</head>
<body>
    <!-- Filigrane -->
    {$watermarkHtml}
    
    <div class="certificate-wrapper">
        <div class="certificate-border">
            <!-- Décorations de coin -->
            <div class="corner-decoration corner-top-left"></div>
            <div class="corner-decoration corner-top-right"></div>
            <div class="corner-decoration corner-bottom-left"></div>
            <div class="corner-decoration corner-bottom-right"></div>
            
            <div class="certificate-content">
                <div class="certificate-header">
                    {$logoHtml}
                    <div class="certificate-title">Certificat de Complétion</div>
                </div>
                
                <div class="certificate-body">
                    <div class="certificate-text">
                        Ce certificat atteste que
                    </div>
                    
                    <div class="certificate-name">{$userName}</div>
                    
                    <div class="certificate-text">
                        a complété avec succès le cours
                    </div>
                    
                    <div class="certificate-course">« {$courseTitle} »</div>
                    
                    <div class="certificate-text" style="margin-top: 1mm;">
                        et a démontré une compréhension approfondie des concepts enseignés.
                    </div>
                </div>
                
                <div class="certificate-footer-section">
                    <div class="certificate-footer">
                        <div class="certificate-signature">
                            <div class="signature-line">
                                <div class="signature-name">{$providerName}</div>
                                <div class="signature-title">Prestataire</div>
                            </div>
                        </div>
                        
                        <div class="certificate-signature">
                            <div class="signature-line">
                                <div class="signature-name">Herime Académie</div>
                                <div class="signature-title">Organisation</div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="certificate-number">
                        <div>Numéro de certificat: <strong>{$certNumber}</strong></div>
                        <div class="certificate-date">Délivré le {$issueDate}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
HTML;
    }

    /**
     * Obtenir le contenu PDF d'un certificat
     */
    public function getCertificatePdfContent(Certificate $certificate): string
    {
        if (!$certificate->file_path) {
            throw new \Exception('Le fichier du certificat n\'existe pas.');
        }

        $filePath = Storage::disk('public')->path($certificate->file_path);

        if (!file_exists($filePath)) {
            throw new \Exception('Le fichier du certificat n\'existe pas sur le serveur.');
        }

        return file_get_contents($filePath);
    }

    /**
     * Régénérer un certificat existant
     * Cette méthode supprime l'ancien PDF et en génère un nouveau avec les mêmes informations
     */
    public function regenerateCertificate(Certificate $certificate): Certificate
    {
        // Charger les relations nécessaires
        if (!$certificate->relationLoaded('user')) {
            $certificate->load('user');
        }
        if (!$certificate->relationLoaded('course')) {
            $certificate->load('course');
        }

        $user = $certificate->user;
        $course = $certificate->course;

        // Supprimer l'ancien fichier PDF si il existe
        if ($certificate->file_path) {
            try {
                Storage::disk('public')->delete($certificate->file_path);
            } catch (\Exception $e) {
                \Log::warning('Erreur lors de la suppression de l\'ancien certificat', [
                    'certificate_id' => $certificate->id,
                    'file_path' => $certificate->file_path,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        // Générer un nouveau PDF avec le même numéro de certificat
        $pdfPath = $this->generatePdf($user, $course, $certificate->certificate_number);

        // Mettre à jour l'enregistrement du certificat
        $certificate->update([
            'file_path' => $pdfPath,
            'issued_at' => now(), // Mettre à jour la date d'émission
            'title' => $course->title, // Mettre à jour le titre au cas où le cours a changé
            'description' => "Certificat de complétion pour le cours : {$course->title}",
        ]);

        return $certificate->fresh();
    }
}

