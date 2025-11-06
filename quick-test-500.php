<?php
/**
 * Script de test rapide pour identifier l'erreur 500
 * Placez ce fichier dans public/ et accédez-y via: http://votre-site.com/quick-test-500.php
 */

// Afficher toutes les erreurs
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);

echo "<h1>Test Diagnostic Erreur 500</h1>";
echo "<pre>";

// Test 1: Vérifier PHP
echo "1. Version PHP: " . phpversion() . "\n";
echo "   ✅ PHP fonctionne\n\n";

// Test 2: Vérifier les fichiers essentiels
echo "2. Vérification des fichiers essentiels:\n";
$files = [
    '../vendor/autoload.php' => 'Autoloader Composer',
    '../bootstrap/app.php' => 'Bootstrap Laravel',
    '../.env' => 'Fichier de configuration',
];

foreach ($files as $file => $name) {
    if (file_exists(__DIR__ . '/' . $file)) {
        echo "   ✅ $name existe\n";
    } else {
        echo "   ❌ $name MANQUANT: $file\n";
    }
}
echo "\n";

// Test 3: Vérifier les permissions
echo "3. Vérification des permissions:\n";
$dirs = [
    '../storage' => 'Storage',
    '../storage/logs' => 'Storage/Logs',
    '../storage/framework' => 'Storage/Framework',
    '../storage/framework/cache' => 'Storage/Framework/Cache',
    '../storage/framework/sessions' => 'Storage/Framework/Sessions',
    '../storage/framework/views' => 'Storage/Framework/Views',
    '../bootstrap/cache' => 'Bootstrap/Cache',
];

foreach ($dirs as $dir => $name) {
    $fullPath = __DIR__ . '/' . $dir;
    if (is_dir($fullPath)) {
        $writable = is_writable($fullPath) ? '✅' : '❌';
        $perms = substr(sprintf('%o', fileperms($fullPath)), -4);
        echo "   $writable $name: $perms (writable: " . (is_writable($fullPath) ? 'OUI' : 'NON') . ")\n";
    } else {
        echo "   ❌ $name: N'existe pas\n";
    }
}
echo "\n";

// Test 4: Tester l'autoloader
echo "4. Test de l'autoloader Composer:\n";
try {
    require_once __DIR__ . '/../vendor/autoload.php';
    echo "   ✅ Autoloader chargé avec succès\n\n";
} catch (Exception $e) {
    echo "   ❌ Erreur: " . $e->getMessage() . "\n\n";
    exit(1);
}

// Test 5: Tester le bootstrap Laravel
echo "5. Test du bootstrap Laravel:\n";
try {
    $app = require_once __DIR__ . '/../bootstrap/app.php';
    echo "   ✅ Bootstrap Laravel chargé avec succès\n\n";
} catch (Exception $e) {
    echo "   ❌ Erreur Bootstrap: " . $e->getMessage() . "\n";
    echo "   Fichier: " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo "   Stack trace:\n";
    echo $e->getTraceAsString() . "\n\n";
    exit(1);
} catch (Error $e) {
    echo "   ❌ Erreur Fatal: " . $e->getMessage() . "\n";
    echo "   Fichier: " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo "   Stack trace:\n";
    echo $e->getTraceAsString() . "\n\n";
    exit(1);
}

// Test 6: Vérifier la configuration
echo "6. Vérification de la configuration:\n";
try {
    if (file_exists(__DIR__ . '/../.env')) {
        $env = file_get_contents(__DIR__ . '/../.env');
        if (strpos($env, 'APP_KEY=') !== false) {
            if (strpos($env, 'APP_KEY=base64:') !== false) {
                echo "   ✅ APP_KEY configuré\n";
            } else {
                echo "   ⚠️  APP_KEY présent mais non généré\n";
            }
        } else {
            echo "   ❌ APP_KEY manquant\n";
        }
        
        if (strpos($env, 'DB_') !== false) {
            echo "   ✅ Configuration DB présente\n";
        } else {
            echo "   ⚠️  Configuration DB absente\n";
        }
    } else {
        echo "   ❌ Fichier .env manquant\n";
    }
} catch (Exception $e) {
    echo "   ❌ Erreur: " . $e->getMessage() . "\n";
}
echo "\n";

// Test 7: Tester une requête simple
echo "7. Test d'une requête Laravel simple:\n";
try {
    $kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
    $response = $kernel->handle(
        $request = Illuminate\Http\Request::capture()
    );
    echo "   ✅ Requête Laravel traitée avec succès\n";
    echo "   Status: " . $response->getStatusCode() . "\n\n";
} catch (Exception $e) {
    echo "   ❌ Erreur lors du traitement de la requête:\n";
    echo "   Message: " . $e->getMessage() . "\n";
    echo "   Fichier: " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo "   Stack trace (10 premières lignes):\n";
    $trace = explode("\n", $e->getTraceAsString());
    foreach (array_slice($trace, 0, 10) as $line) {
        echo "      " . $line . "\n";
    }
    echo "\n";
} catch (Error $e) {
    echo "   ❌ Erreur Fatal lors du traitement:\n";
    echo "   Message: " . $e->getMessage() . "\n";
    echo "   Fichier: " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo "   Stack trace (10 premières lignes):\n";
    $trace = explode("\n", $e->getTraceAsString());
    foreach (array_slice($trace, 0, 10) as $line) {
        echo "      " . $line . "\n";
    }
    echo "\n";
}

// Test 8: Vérifier les logs
echo "8. Dernières erreurs dans les logs:\n";
$logFile = __DIR__ . '/../storage/logs/laravel.log';
if (file_exists($logFile)) {
    $lastErrors = shell_exec("tail -50 '$logFile' 2>/dev/null | grep -E 'ERROR|CRITICAL|Exception|Fatal' | tail -3");
    if ($lastErrors) {
        echo "   Dernières erreurs:\n";
        echo "   " . str_replace("\n", "\n   ", trim($lastErrors)) . "\n";
    } else {
        echo "   ✅ Aucune erreur récente dans les logs\n";
    }
} else {
    echo "   ⚠️  Fichier de log non trouvé\n";
}

echo "\n";
echo "==========================================\n";
echo "FIN DU TEST\n";
echo "==========================================\n";
echo "</pre>";

