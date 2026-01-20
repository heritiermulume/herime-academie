<?php

namespace App\Console\Commands;

use App\Models\Course;
use Illuminate\Console\Command;

class RecalculateCourseStatistics extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'courses:recalculate-stats {--course= : ID du cours spécifique} {--all : Recalculer toutes les statistiques}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Recalculer les statistiques des contenus (nombre de clients, notes, etc.)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔄 Recalcul des statistiques des cours...');

        if ($this->option('course')) {
            $this->recalculateSingleCourse($this->option('course'));
        } elseif ($this->option('all')) {
            $this->recalculateAllCourses();
        } else {
            $this->error('Veuillez spécifier --course=ID ou --all');
            return 1;
        }

        $this->info('✅ Recalcul terminé avec succès!');
        return 0;
    }

    private function recalculateSingleCourse($contentId)
    {
        $course = Course::find($contentId);
        
        if (!$course) {
            $this->error("Cours avec l'ID {$contentId} non trouvé.");
            return;
        }

        $this->info("Recalcul des statistiques pour: {$course->title}");
        $this->displayCourseStats($course);
    }

    private function recalculateAllCourses()
    {
        $courses = Course::with(['enrollments', 'reviews', 'sections.lessons'])->get();
        $bar = $this->output->createProgressBar($courses->count());
        $bar->start();

        foreach ($courses as $course) {
            $this->displayCourseStats($course);
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
    }

    private function displayCourseStats(Course $course)
    {
        $stats = $course->getCourseStats();
        
        $this->line("📊 {$course->title}:");
        $this->line("   👥 Clients: {$stats['total_customers']}");
        $this->line("   ⭐ Note: " . number_format($stats['average_rating'], 1) . " ({$stats['total_reviews']} avis)");
        $this->line("   📚 Leçons: {$stats['total_lessons']}");
        $this->line("   ⏱️  Durée: {$stats['total_duration']} min");
        $this->newLine();
    }
}