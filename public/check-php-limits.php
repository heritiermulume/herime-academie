<?php
/**
 * Script de diagnostic des limites PHP
 * À utiliser UNIQUEMENT pour vérifier la configuration
 * SUPPRIMER après vérification pour la sécurité !
 */

// Empêcher l'accès public non autorisé
$secret_key = 'herime2024'; // Changez cette clé !
if (!isset($_GET['key']) || $_GET['key'] !== $secret_key) {
    die('Accès non autorisé');
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vérification des limites PHP</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #003366;
            border-bottom: 3px solid #003366;
            padding-bottom: 10px;
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 15px;
            margin: 10px 0;
            border-radius: 5px;
            background: #f8f9fa;
        }
        .label {
            font-weight: bold;
            color: #333;
        }
        .value {
            color: #003366;
            font-weight: bold;
        }
        .ok {
            background: #d4edda;
            border-left: 4px solid #28a745;
        }
        .warning {
            background: #fff3cd;
            border-left: 4px solid #ffc107;
        }
        .error {
            background: #f8d7da;
            border-left: 4px solid #dc3545;
        }
        .status {
            padding: 5px 10px;
            border-radius: 3px;
            font-size: 12px;
            margin-left: 10px;
        }
        .status.ok { background: #28a745; color: white; }
        .status.warning { background: #ffc107; color: black; }
        .status.error { background: #dc3545; color: white; }
        .alert {
            padding: 15px;
            margin: 20px 0;
            border-radius: 5px;
            background: #fff3cd;
            border: 1px solid #ffc107;
        }
        .alert.error {
            background: #f8d7da;
            border: 1px solid #dc3545;
        }
        .alert.success {
            background: #d4edda;
            border: 1px solid #28a745;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 Vérification des limites PHP</h1>
        
        <div class="alert error">
            <strong>⚠️ IMPORTANT :</strong> Supprimez ce fichier après vérification pour la sécurité !<br>
            Commande : <code>rm public/check-php-limits.php</code>
        </div>

        <?php
        // Récupérer les valeurs actuelles
        $upload_max = ini_get('upload_max_filesize');
        $post_max = ini_get('post_max_size');
        $memory = ini_get('memory_limit');
        $max_execution = ini_get('max_execution_time');
        $max_input_time = ini_get('max_input_time');
        
        // Convertir en bytes pour comparaison
        function parse_size($size) {
            $unit = preg_replace('/[^bkmgtpezy]/i', '', $size);
            $size = preg_replace('/[^0-9\.]/', '', $size);
            if ($unit) {
                return round($size * pow(1024, stripos('bkmgtpezy', $unit[0])));
            }
            return round($size);
        }
        
        $upload_bytes = parse_size($upload_max);
        $post_bytes = parse_size($post_max);
        $memory_bytes = parse_size($memory);
        
        // Valeurs recommandées
        $upload_required = parse_size('20M');
        $post_required = parse_size('30M');
        $memory_required = parse_size('512M');
        
        $upload_ok = $upload_bytes >= $upload_required;
        $post_ok = $post_bytes >= $post_required;
        $memory_ok = $memory_bytes >= $memory_required;
        $all_ok = $upload_ok && $post_ok && $memory_ok;
        ?>

        <?php if ($all_ok): ?>
        <div class="alert success">
            <strong>✅ Configuration correcte !</strong> Les limites PHP sont suffisantes pour l'upload de bannières.
        </div>
        <?php else: ?>
        <div class="alert error">
            <strong>❌ Configuration insuffisante !</strong> Certaines limites doivent être augmentées.
        </div>
        <?php endif; ?>

        <h2>📊 Limites actuelles</h2>

        <div class="info-row <?php echo $upload_ok ? 'ok' : 'error'; ?>">
            <span class="label">upload_max_filesize</span>
            <span>
                <span class="value"><?php echo $upload_max; ?></span>
                <span class="status <?php echo $upload_ok ? 'ok' : 'error'; ?>">
                    <?php echo $upload_ok ? '✓ OK' : '✗ Requis: ≥ 20M'; ?>
                </span>
            </span>
        </div>

        <div class="info-row <?php echo $post_ok ? 'ok' : 'error'; ?>">
            <span class="label">post_max_size</span>
            <span>
                <span class="value"><?php echo $post_max; ?></span>
                <span class="status <?php echo $post_ok ? 'ok' : 'error'; ?>">
                    <?php echo $post_ok ? '✓ OK' : '✗ Requis: ≥ 30M'; ?>
                </span>
            </span>
        </div>

        <div class="info-row <?php echo $memory_ok ? 'ok' : 'warning'; ?>">
            <span class="label">memory_limit</span>
            <span>
                <span class="value"><?php echo $memory; ?></span>
                <span class="status <?php echo $memory_ok ? 'ok' : 'warning'; ?>">
                    <?php echo $memory_ok ? '✓ OK' : '⚠ Recommandé: ≥ 512M'; ?>
                </span>
            </span>
        </div>

        <div class="info-row ok">
            <span class="label">max_execution_time</span>
            <span class="value"><?php echo $max_execution; ?> secondes</span>
        </div>

        <div class="info-row ok">
            <span class="label">max_input_time</span>
            <span class="value"><?php echo $max_input_time; ?> secondes</span>
        </div>

        <h2>ℹ️ Informations système</h2>

        <div class="info-row">
            <span class="label">Version PHP</span>
            <span class="value"><?php echo PHP_VERSION; ?></span>
        </div>

        <div class="info-row">
            <span class="label">Serveur Web</span>
            <span class="value"><?php echo $_SERVER['SERVER_SOFTWARE'] ?? 'Inconnu'; ?></span>
        </div>

        <div class="info-row">
            <span class="label">Fichier de configuration chargé</span>
            <span class="value" style="font-size: 11px;"><?php echo php_ini_loaded_file(); ?></span>
        </div>

        <?php if (!$all_ok): ?>
        <h2>🔧 Comment corriger ?</h2>
        <div class="alert">
            <strong>Solution :</strong> Vérifiez que votre fichier <code>.htaccess</code> contient bien ces lignes :
            <pre style="background: #f8f9fa; padding: 15px; border-radius: 5px; overflow-x: auto;">
# Augmenter les limites PHP pour les uploads
php_value upload_max_filesize 20M
php_value post_max_size 30M
php_value memory_limit 512M</pre>
            
            <p>Puis exécutez :</p>
            <pre style="background: #f8f9fa; padding: 15px; border-radius: 5px;">
git pull origin main
./fix-banner-storage.sh</pre>
        </div>
        <?php endif; ?>

        <div class="alert">
            <strong>🗑️ N'oubliez pas :</strong> Supprimez ce fichier après vérification !<br>
            <code>rm public/check-php-limits.php</code>
        </div>
    </div>
</body>
</html>

