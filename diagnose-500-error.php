<?php

/**
 * Script de diagnostic pour identifier les erreurs 500 en production
 * Usage: php diagnose-500-error.php
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

echo "========================================\n";
echo "DIAGNOSTIC ERREUR 500 - HERIME ACADEMIE\n";
echo "========================================\n\n";

// 1. Vérifier les logs récents
echo "1. ANALYSE DES LOGS RECENTS\n";
echo "----------------------------\n";
$logFile = storage_path('logs/laravel.log');
if (file_exists($logFile)) {
    $logContent = shell_exec("tail -200 $logFile 2>/dev/null");
    $errors = [];
    
    // Extraire les erreurs récentes
    $lines = explode("\n", $logContent);
    $currentError = null;
    
    foreach ($lines as $line) {
        if (preg_match('/\[(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})\] local\.(ERROR|CRITICAL): (.+)/', $line, $matches)) {
            $date = $matches[1];
            $level = $matches[2];
            $message = $matches[3];
            
            $errors[] = [
                'date' => $date,
                'level' => $level,
                'message' => substr($message, 0, 200),
            ];
        }
    }
    
    if (empty($errors)) {
        echo "✓ Aucune erreur récente dans les logs\n";
    } else {
        echo "✗ " . count($errors) . " erreur(s) trouvée(s) :\n\n";
        foreach (array_slice($errors, -5) as $error) {
            echo "  [" . $error['date'] . "] " . $error['level'] . ": " . $error['message'] . "\n";
        }
    }
} else {
    echo "⚠ Fichier de log non trouvé: $logFile\n";
}

echo "\n";

// 2. Vérifier la configuration de la base de données
echo "2. VERIFICATION BASE DE DONNEES\n";
echo "-------------------------------\n";
try {
    DB::connection()->getPdo();
    echo "✓ Connexion à la base de données: OK\n";
    
    // Vérifier si la colonne video_url existe
    $columns = DB::select('SHOW COLUMNS FROM course_lessons');
    $hasVideoUrl = false;
    foreach ($columns as $column) {
        if ($column->Field === 'video_url') {
            $hasVideoUrl = true;
            break;
        }
    }
    
    if ($hasVideoUrl) {
        echo "⚠ ATTENTION: La colonne 'video_url' existe dans course_lessons\n";
    } else {
        echo "✓ Colonne 'video_url' n'existe pas (normal)\n";
    }
} catch (\Exception $e) {
    echo "✗ Erreur de connexion à la base de données: " . $e->getMessage() . "\n";
}

echo "\n";

// 3. Tester les routes principales
echo "3. TEST DES ROUTES PRINCIPALES\n";
echo "-------------------------------\n";

$routesToTest = [
    '/' => 'GET',
    '/courses' => 'GET',
];

$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

foreach ($routesToTest as $route => $method) {
    try {
        $request = Illuminate\Http\Request::create($route, $method);
        $response = $kernel->handle($request);
        $status = $response->getStatusCode();
        
        if ($status < 500) {
            echo "✓ $route: Status $status\n";
        } else {
            echo "✗ $route: Status $status (ERREUR SERVEUR)\n";
        }
    } catch (\Exception $e) {
        echo "✗ $route: EXCEPTION - " . $e->getMessage() . "\n";
        echo "   Fichier: " . $e->getFile() . ":" . $e->getLine() . "\n";
    }
}

echo "\n";

// 4. Tester previewData avec un cours réel
echo "4. TEST DE LA METHODE previewData\n";
echo "----------------------------------\n";
try {
    $course = \App\Models\Course::first();
    if ($course) {
        $controller = app(\App\Http\Controllers\CourseController::class);
        $response = $controller->previewData($course);
        $status = $response->getStatusCode();
        
        if ($status === 200) {
            echo "✓ previewData: OK (Status $status)\n";
        } else {
            echo "✗ previewData: Status $status\n";
        }
    } else {
        echo "⚠ Aucun cours trouvé pour tester previewData\n";
    }
} catch (\Exception $e) {
    echo "✗ previewData: EXCEPTION - " . $e->getMessage() . "\n";
    echo "   Fichier: " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo "   Stack trace:\n";
    $trace = $e->getTraceAsString();
    $traceLines = explode("\n", $trace);
    foreach (array_slice($traceLines, 0, 10) as $traceLine) {
        echo "   " . $traceLine . "\n";
    }
}

echo "\n";

// 5. Tester le dashboard étudiant
echo "5. TEST DU DASHBOARD ETUDIANT\n";
echo "------------------------------\n";
try {
    $user = \App\Models\User::where('role', 'student')->first();
    if ($user) {
        Auth::login($user);
        $controller = app(\App\Http\Controllers\StudentController::class);
        $view = $controller->dashboard();
        echo "✓ Dashboard étudiant: Vue générée avec succès\n";
    } else {
        echo "⚠ Aucun utilisateur étudiant trouvé pour tester le dashboard\n";
    }
} catch (\Exception $e) {
    echo "✗ Dashboard étudiant: EXCEPTION - " . $e->getMessage() . "\n";
    echo "   Fichier: " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo "   Stack trace:\n";
    $trace = $e->getTraceAsString();
    $traceLines = explode("\n", $trace);
    foreach (array_slice($traceLines, 0, 10) as $traceLine) {
        echo "   " . $traceLine . "\n";
    }
}

echo "\n";

// 6. Vérifier les permissions de fichiers
echo "6. VERIFICATION PERMISSIONS\n";
echo "----------------------------\n";
$directories = [
    storage_path('logs'),
    storage_path('framework/cache'),
    storage_path('framework/sessions'),
    storage_path('framework/views'),
    base_path('bootstrap/cache'),
];

foreach ($directories as $dir) {
    if (is_dir($dir)) {
        $writable = is_writable($dir);
        echo ($writable ? "✓" : "✗") . " $dir: " . ($writable ? "Écriture OK" : "NON ÉCRITABLE") . "\n";
    } else {
        echo "⚠ $dir: N'existe pas\n";
    }
}

echo "\n";

// 7. Vérifier les erreurs PHP récentes
echo "7. ERREURS PHP RECENTES\n";
echo "-----------------------\n";
$errorLog = ini_get('error_log');
if ($errorLog && file_exists($errorLog)) {
    $phpErrors = shell_exec("tail -50 $errorLog 2>/dev/null | grep -i 'error\\|fatal\\|warning' | tail -5");
    if ($phpErrors) {
        echo "Erreurs PHP récentes:\n";
        echo $phpErrors . "\n";
    } else {
        echo "✓ Aucune erreur PHP récente\n";
    }
} else {
    echo "⚠ Fichier de log PHP non trouvé ou non configuré\n";
}

echo "\n";

// 8. Vérifier la configuration SSO
echo "8. VERIFICATION CONFIGURATION SSO\n";
echo "----------------------------------\n";
$ssoEnabled = config('services.sso.enabled');
$ssoBaseUrl = config('services.sso.base_url');
$ssoSecret = config('services.sso.secret');

echo "SSO Enabled: " . ($ssoEnabled ? "Oui" : "Non") . "\n";
echo "SSO Base URL: " . ($ssoBaseUrl ?: "Non configuré") . "\n";
echo "SSO Secret: " . ($ssoSecret ? "Configuré" : "NON CONFIGURÉ") . "\n";

echo "\n";

// 9. Tester les routes avec authentification
echo "9. TEST DES ROUTES AUTHENTIFIEES\n";
echo "---------------------------------\n";
try {
    $user = \App\Models\User::first();
    if ($user) {
        Auth::login($user);
        
        $routesToTest = [
            '/dashboard',
            '/student/dashboard',
        ];
        
        foreach ($routesToTest as $route) {
            try {
                $request = Illuminate\Http\Request::create($route, 'GET');
                $response = $kernel->handle($request);
                $status = $response->getStatusCode();
                
                if ($status < 500) {
                    echo "✓ $route: Status $status\n";
                } else {
                    echo "✗ $route: Status $status (ERREUR SERVEUR)\n";
                }
            } catch (\Exception $e) {
                echo "✗ $route: EXCEPTION - " . $e->getMessage() . "\n";
            }
        }
    } else {
        echo "⚠ Aucun utilisateur trouvé pour tester les routes authentifiées\n";
    }
} catch (\Exception $e) {
    echo "✗ Erreur lors du test des routes authentifiées: " . $e->getMessage() . "\n";
}

echo "\n";

// 10. Vérifier les dernières erreurs avec détails complets
echo "10. DERNIERES ERREURS DETAILLEES\n";
echo "---------------------------------\n";
if (file_exists($logFile)) {
    $lastError = shell_exec("tail -1000 $logFile 2>/dev/null | grep -A 50 'local.ERROR\\|local.CRITICAL' | tail -100");
    if ($lastError) {
        echo "Dernière erreur complète:\n";
        echo substr($lastError, 0, 2000) . "\n";
        if (strlen($lastError) > 2000) {
            echo "... (tronqué)\n";
        }
    } else {
        echo "✓ Aucune erreur détaillée trouvée dans les 1000 dernières lignes\n";
    }
}

echo "\n";

// 11. Vérifier la configuration PHP
echo "11. CONFIGURATION PHP\n";
echo "----------------------\n";
echo "Version PHP: " . PHP_VERSION . "\n";
echo "Memory Limit: " . ini_get('memory_limit') . "\n";
echo "Max Execution Time: " . ini_get('max_execution_time') . "\n";
echo "Upload Max Filesize: " . ini_get('upload_max_filesize') . "\n";
echo "Post Max Size: " . ini_get('post_max_size') . "\n";

$requiredExtensions = ['pdo', 'pdo_mysql', 'mbstring', 'openssl', 'tokenizer', 'json', 'curl', 'fileinfo'];
echo "\nExtensions PHP requises:\n";
foreach ($requiredExtensions as $ext) {
    $loaded = extension_loaded($ext);
    echo ($loaded ? "✓" : "✗") . " $ext: " . ($loaded ? "Chargée" : "MANQUANTE") . "\n";
}

echo "\n";

// 12. Résumé et recommandations
echo "12. RECOMMANDATIONS\n";
echo "-------------------\n";
echo "Si l'erreur 500 persiste:\n";
echo "1. Vérifiez les logs en temps réel: tail -f storage/logs/laravel.log\n";
echo "2. Activez le mode debug temporairement: APP_DEBUG=true dans .env\n";
echo "3. Videz tous les caches: php artisan optimize:clear\n";
echo "4. Vérifiez les permissions: chmod -R 775 storage bootstrap/cache\n";
echo "5. Redémarrez PHP-FPM/Apache si nécessaire\n";
echo "6. Vérifiez les logs du serveur web (Apache/Nginx)\n";
echo "7. Vérifiez que toutes les migrations sont exécutées\n";
echo "8. Vérifiez la connexion à la base de données\n";

echo "\n";
echo "========================================\n";
echo "FIN DU DIAGNOSTIC\n";
echo "========================================\n";
echo "\nPour exécuter ce script en production:\n";
echo "php diagnose-500-error.php > diagnostic-result.txt 2>&1\n";

