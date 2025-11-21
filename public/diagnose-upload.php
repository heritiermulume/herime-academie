<?php
/**
 * Script de diagnostic pour les problèmes d'upload
 * 
 * ⚠️ IMPORTANT: Supprimez ce fichier après diagnostic pour des raisons de sécurité
 * 
 * Accédez à: https://votre-site.com/diagnose-upload.php
 */

header('Content-Type: application/json; charset=utf-8');

$diagnostics = [
    'timestamp' => date('Y-m-d H:i:s'),
    'php_version' => PHP_VERSION,
    'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
];

// Vérifier les limites PHP
$diagnostics['php_limits'] = [
    'upload_max_filesize' => ini_get('upload_max_filesize'),
    'post_max_size' => ini_get('post_max_size'),
    'max_execution_time' => ini_get('max_execution_time'),
    'max_input_time' => ini_get('max_input_time'),
    'memory_limit' => ini_get('memory_limit'),
    'max_file_uploads' => ini_get('max_file_uploads'),
];

// Convertir en octets pour comparaison
function parseSize($size) {
    $size = trim($size);
    $last = strtolower($size[strlen($size) - 1] ?? '');
    $size = (int) $size;
    switch ($last) {
        case 'g': $size *= 1024;
        case 'm': $size *= 1024;
        case 'k': $size *= 1024;
    }
    return $size;
}

$uploadMaxBytes = parseSize(ini_get('upload_max_filesize'));
$postMaxBytes = parseSize(ini_get('post_max_size'));

$diagnostics['php_limits_parsed'] = [
    'upload_max_filesize_bytes' => $uploadMaxBytes,
    'post_max_size_bytes' => $postMaxBytes,
    'post_max_size_ok' => $postMaxBytes >= $uploadMaxBytes,
];

// Vérifier les permissions
$storagePath = __DIR__ . '/../storage/app';
$tmpPath = __DIR__ . '/../storage/app/tmp';

$diagnostics['permissions'] = [
    'storage_app_exists' => is_dir($storagePath),
    'storage_app_writable' => is_dir($storagePath) && is_writable($storagePath),
    'storage_app_permissions' => is_dir($storagePath) ? substr(sprintf('%o', fileperms($storagePath)), -4) : 'N/A',
    'tmp_exists' => is_dir($tmpPath),
    'tmp_writable' => is_dir($tmpPath) && is_writable($tmpPath),
    'tmp_permissions' => is_dir($tmpPath) ? substr(sprintf('%o', fileperms($tmpPath)), -4) : 'N/A',
];

// Vérifier l'espace disque
$diagnostics['disk_space'] = [
    'free_space' => disk_free_space($storagePath ?? __DIR__),
    'free_space_mb' => round(disk_free_space($storagePath ?? __DIR__) / 1024 / 1024, 2),
    'total_space' => disk_total_space($storagePath ?? __DIR__),
    'total_space_mb' => round(disk_total_space($storagePath ?? __DIR__) / 1024 / 1024, 2),
];

// Vérifier les erreurs PHP
$diagnostics['php_errors'] = [
    'display_errors' => ini_get('display_errors'),
    'error_reporting' => error_reporting(),
    'log_errors' => ini_get('log_errors'),
    'error_log' => ini_get('error_log'),
];

// Vérifier les extensions nécessaires
$diagnostics['extensions'] = [
    'fileinfo' => extension_loaded('fileinfo'),
    'gd' => extension_loaded('gd'),
    'imagick' => extension_loaded('imagick'),
    'zip' => extension_loaded('zip'),
];

// Vérifier les variables d'environnement Laravel
$envPath = __DIR__ . '/../.env';
$diagnostics['laravel'] = [
    'env_file_exists' => file_exists($envPath),
    'app_env' => getenv('APP_ENV') ?: 'Not set',
    'app_debug' => getenv('APP_DEBUG') ?: 'Not set',
];

// Vérifier les logs Laravel
$logPath = __DIR__ . '/../storage/logs/laravel.log';
$logsPath = __DIR__ . '/../storage/logs';
$diagnostics['laravel_logs'] = [
    'logs_directory_exists' => is_dir($logsPath),
    'logs_directory_writable' => is_dir($logsPath) && is_writable($logsPath),
    'laravel_log_exists' => file_exists($logPath),
    'laravel_log_readable' => file_exists($logPath) && is_readable($logPath),
    'laravel_log_size' => file_exists($logPath) ? filesize($logPath) : 0,
    'laravel_log_size_mb' => file_exists($logPath) ? round(filesize($logPath) / 1024 / 1024, 2) : 0,
    'last_log_entry' => file_exists($logPath) && filesize($logPath) > 0 
        ? substr(file_get_contents($logPath), -500) 
        : 'No log entries',
];

// Test d'écriture
$testFile = $tmpPath . '/test_write_' . time() . '.txt';
$writeTest = false;
if (is_dir($tmpPath) && is_writable($tmpPath)) {
    $writeTest = @file_put_contents($testFile, 'test');
    if ($writeTest !== false) {
        @unlink($testFile);
    }
}

$diagnostics['write_test'] = [
    'can_write' => $writeTest !== false,
    'test_file' => $testFile,
];

// Recommandations
$recommendations = [];

if ($postMaxBytes < $uploadMaxBytes) {
    $recommendations[] = '⚠️ post_max_size doit être >= upload_max_filesize';
}

if ($uploadMaxBytes < 100 * 1024 * 1024) {
    $recommendations[] = '⚠️ upload_max_filesize est trop faible (recommandé: au moins 100M)';
}

if (ini_get('max_execution_time') < 300) {
    $recommendations[] = '⚠️ max_execution_time est trop faible pour les gros fichiers (recommandé: au moins 300)';
}

if (!is_dir($storagePath) || !is_writable($storagePath)) {
    $recommendations[] = '❌ Le dossier storage/app n\'existe pas ou n\'est pas accessible en écriture';
}

if (!is_dir($tmpPath) || !is_writable($tmpPath)) {
    $recommendations[] = '❌ Le dossier storage/app/tmp n\'existe pas ou n\'est pas accessible en écriture';
}

if ($writeTest === false) {
    $recommendations[] = '❌ Impossible d\'écrire dans le dossier temporaire';
}

if (ini_get('error_log') === '/dev/null') {
    $recommendations[] = '⚠️ Les erreurs PHP sont redirigées vers /dev/null. Les logs Laravel dans storage/logs/laravel.log devraient toujours fonctionner.';
}

if (!is_dir($logsPath) || !is_writable($logsPath)) {
    $recommendations[] = '❌ Le dossier storage/logs n\'existe pas ou n\'est pas accessible en écriture';
}

$diagnostics['recommendations'] = $recommendations;
$diagnostics['status'] = empty($recommendations) ? 'OK' : 'ISSUES_FOUND';

// Instructions pour vérifier les logs
$diagnostics['next_steps'] = [
    '1' => 'Vérifier les logs Laravel: tail -f storage/logs/laravel.log',
    '2' => 'Tester un upload et observer les logs en temps réel',
    '3' => 'Vérifier les dernières erreurs dans les logs',
    '4' => 'Si aucune erreur n\'apparaît, l\'erreur peut se produire avant que Laravel ne traite la requête',
];

echo json_encode($diagnostics, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

