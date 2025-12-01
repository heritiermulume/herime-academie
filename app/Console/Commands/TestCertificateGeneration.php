<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\Course;
use App\Services\CertificateService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class TestCertificateGeneration extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'certificate:test';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test certificate generation to verify it fits on one page';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Testing certificate generation...');

        // Récupérer un utilisateur et un cours de test
        $user = User::first();
        $course = Course::with('instructor')->first();

        if (!$user || !$course) {
            $this->error('No user or course found. Please create at least one user and one course.');
            return 1;
        }

        $this->info("Generating certificate for user: {$user->name}");
        $this->info("Course: {$course->title}");

        try {
            $certificateService = app(CertificateService::class);
            $certificate = $certificateService->generateCertificate($user, $course);

            $this->info("Certificate generated successfully!");
            $this->info("Certificate number: {$certificate->certificate_number}");
            $this->info("File path: {$certificate->file_path}");

            // Vérifier le fichier
            $filePath = Storage::disk('public')->path($certificate->file_path);
            
            if (file_exists($filePath)) {
                $fileSize = filesize($filePath);
                $this->info("File size: " . number_format($fileSize / 1024, 2) . " KB");
                
                // Lire le PDF pour vérifier le nombre de pages
                $pdfContent = file_get_contents($filePath);
                $pageCount = preg_match_all('/\/Type[\s]*\/Page[^s]/', $pdfContent);
                
                $this->info("Number of pages detected: {$pageCount}");
                
                if ($pageCount == 1) {
                    $this->info("✓ SUCCESS: Certificate is on one page!");
                } else {
                    $this->warn("⚠ WARNING: Certificate has {$pageCount} pages (expected 1)");
                }
                
                $this->info("\nCertificate saved at: {$filePath}");
            } else {
                $this->error("Certificate file not found at: {$filePath}");
            }

            return 0;
        } catch (\Exception $e) {
            $this->error("Error generating certificate: " . $e->getMessage());
            $this->error($e->getTraceAsString());
            return 1;
        }
    }
}
