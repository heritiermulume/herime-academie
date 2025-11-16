<?php

/**
 * Script de test simple pour identifier l'erreur 500 en production
 * Usage: php test-production-500.php
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

echo "========================================\n";
echo "TEST ERREUR 500 - PRODUCTION\n";
echo "========================================\n\n";

// Test 1: Vérifier la dernière erreur exacte dans les logs
echo "1. DERNIERE ERREUR EXACTE\n";
echo "-------------------------\n";
$logFile = storage_path('logs/laravel.log');
if (file_exists($logFile)) {
    $lastError = shell_exec("tail -500 $logFile 2>/dev/null | grep -B 5 -A 30 'SQLSTATE\\|Exception\\|Fatal' | tail -40");
    if ($lastError) {
        echo $lastError . "\n\n";
    } else {
        echo "Aucune erreur SQL/Exception trouvée dans les 500 dernières lignes\n\n";
    }
}

// Test 2: Tester previewData avec un cours réel
echo "2. TEST previewData\n";
echo "-------------------\n";
try {
    $course = \App\Models\Course::with('sections')->first();
    if ($course) {
        echo "Cours trouvé: ID {$course->id}, Titre: {$course->title}\n";
        
        // Test de la requête exacte qui cause l'erreur
        try {
            $sections = $course->sections()->with(['lessons' => function($query) {
                $query->where('type', 'video')
                      ->where('is_published', true)
                      ->where(function($q) {
                          $q->whereNotNull('youtube_video_id')
                            ->orWhereNotNull('file_path')
                            ->orWhereNotNull('content_url');
                      })
                      ->orderBy('sort_order');
            }])->get();
            
            echo "✓ Requête SQL exécutée avec succès\n";
            echo "  Sections trouvées: " . $sections->count() . "\n";
            
            foreach ($sections as $section) {
                echo "  - Section {$section->id}: " . $section->lessons->count() . " leçons\n";
            }
        } catch (\Exception $e) {
            echo "✗ ERREUR SQL: " . $e->getMessage() . "\n";
            echo "  Fichier: " . $e->getFile() . ":" . $e->getLine() . "\n";
            
            // Afficher la requête SQL si disponible
            if (method_exists($e, 'getSql')) {
                echo "  SQL: " . $e->getSql() . "\n";
            }
            if (isset($e->errorInfo) && is_array($e->errorInfo)) {
                echo "  Error Info: " . print_r($e->errorInfo, true) . "\n";
            }
        }
        
        // Test de previewData complète
        try {
            $controller = app(\App\Http\Controllers\CourseController::class);
            $response = $controller->previewData($course);
            echo "✓ previewData complète: Status " . $response->getStatusCode() . "\n";
        } catch (\Exception $e) {
            echo "✗ previewData ERREUR: " . $e->getMessage() . "\n";
            echo "  Fichier: " . $e->getFile() . ":" . $e->getLine() . "\n";
            echo "  Stack trace (10 premières lignes):\n";
            $trace = explode("\n", $e->getTraceAsString());
            foreach (array_slice($trace, 0, 10) as $line) {
                echo "    " . $line . "\n";
            }
        }
    } else {
        echo "⚠ Aucun cours trouvé\n";
    }
} catch (\Exception $e) {
    echo "✗ ERREUR: " . $e->getMessage() . "\n";
}

echo "\n";

// Test 3: Tester addCourseStatistics
echo "3. TEST addCourseStatistics\n";
echo "----------------------------\n";
try {
    $courses = \App\Models\Course::with(['sections.lessons', 'enrollments', 'reviews'])->limit(1)->get();
    if ($courses->count() > 0) {
        $course = $courses->first();
        
        // Simuler addCourseStatistics
        try {
            $stats = [
                'total_lessons' => $course->sections->sum(function($section) {
                    return $section->lessons->count();
                }),
                'total_duration' => $course->sections->sum(function($section) {
                    return $section->lessons->sum('duration');
                }),
                'total_students' => $course->enrollments->count(),
                'average_rating' => $course->reviews->avg('rating') ?? 0,
                'total_reviews' => $course->reviews->count(),
            ];
            echo "✓ addCourseStatistics: OK\n";
            echo "  Stats calculées: " . json_encode($stats) . "\n";
        } catch (\Exception $e) {
            echo "✗ addCourseStatistics ERREUR: " . $e->getMessage() . "\n";
            echo "  Fichier: " . $e->getFile() . ":" . $e->getLine() . "\n";
        }
    } else {
        echo "⚠ Aucun cours trouvé\n";
    }
} catch (\Exception $e) {
    echo "✗ ERREUR: " . $e->getMessage() . "\n";
}

echo "\n";

// Test 4: Vérifier la requête SQL exacte qui échoue
echo "4. REQUETE SQL EXACTE\n";
echo "--------------------\n";
try {
    // Reproduire exactement la requête qui cause l'erreur
    $course = \App\Models\Course::first();
    if ($course) {
        // Activer le logging des requêtes
        DB::enableQueryLog();
        
        $allVideoLessons = $course->sections()
            ->with(['lessons' => function($query) {
                $query->where('type', 'video')
                      ->where('is_published', true)
                      ->where(function($q) {
                          $q->whereNotNull('youtube_video_id')
                            ->orWhereNotNull('file_path')
                            ->orWhereNotNull('content_url');
                      })
                      ->orderBy('sort_order');
            }])
            ->get();
        
        $queries = DB::getQueryLog();
        echo "Requêtes SQL exécutées:\n";
        foreach ($queries as $query) {
            echo "  SQL: " . $query['query'] . "\n";
            if (strpos($query['query'], 'video_url') !== false) {
                echo "  ⚠ ATTENTION: Cette requête contient 'video_url'!\n";
            }
            if (isset($query['bindings']) && !empty($query['bindings'])) {
                echo "  Bindings: " . json_encode($query['bindings']) . "\n";
            }
        }
        
        echo "✓ Requêtes exécutées sans erreur\n";
    }
} catch (\Exception $e) {
    echo "✗ ERREUR SQL: " . $e->getMessage() . "\n";
    if (method_exists($e, 'getSql')) {
        echo "  SQL: " . $e->getSql() . "\n";
    }
}

echo "\n";

// Test 5: Vérifier les colonnes de la table course_lessons
echo "5. COLONNES DE course_lessons\n";
echo "-----------------------------\n";
try {
    $columns = DB::select('SHOW COLUMNS FROM course_lessons');
    echo "Colonnes trouvées:\n";
    foreach ($columns as $column) {
        echo "  - {$column->Field} ({$column->Type})\n";
        if ($column->Field === 'video_url') {
            echo "    ⚠ ATTENTION: Cette colonne existe mais ne devrait pas!\n";
        }
    }
} catch (\Exception $e) {
    echo "✗ Erreur: " . $e->getMessage() . "\n";
}

echo "\n";
echo "========================================\n";
echo "FIN DU TEST\n";
echo "========================================\n";
















