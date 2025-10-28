<?php
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Test des limites PHP</title>
    <style>
        body { font-family: Arial; max-width: 600px; margin: 50px auto; padding: 20px; }
        .ok { color: green; font-weight: bold; }
        .error { color: red; font-weight: bold; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th, td { padding: 10px; text-align: left; border: 1px solid #ddd; }
        th { background: #003366; color: white; }
    </style>
</head>
<body>
    <h1>🔍 Limites PHP Actuelles</h1>
    
    <table>
        <tr>
            <th>Configuration</th>
            <th>Valeur actuelle</th>
            <th>Requis</th>
            <th>Statut</th>
        </tr>
        <?php
        $upload = ini_get('upload_max_filesize');
        $post = ini_get('post_max_size');
        $memory = ini_get('memory_limit');
        
        function parse_size($size) {
            $unit = preg_replace('/[^bkmgtpezy]/i', '', $size);
            $size = preg_replace('/[^0-9\.]/', '', $size);
            if ($unit) {
                return round($size * pow(1024, stripos('bkmgtpezy', $unit[0])));
            }
            return round($size);
        }
        
        $upload_bytes = parse_size($upload);
        $post_bytes = parse_size($post);
        
        $upload_ok = $upload_bytes >= parse_size('20M');
        $post_ok = $post_bytes >= parse_size('30M');
        ?>
        <tr>
            <td><strong>upload_max_filesize</strong></td>
            <td><?php echo $upload; ?></td>
            <td>≥ 20M</td>
            <td class="<?php echo $upload_ok ? 'ok' : 'error'; ?>">
                <?php echo $upload_ok ? '✅ OK' : '❌ INSUFFISANT'; ?>
            </td>
        </tr>
        <tr>
            <td><strong>post_max_size</strong></td>
            <td><?php echo $post; ?></td>
            <td>≥ 30M</td>
            <td class="<?php echo $post_ok ? 'ok' : 'error'; ?>">
                <?php echo $post_ok ? '✅ OK' : '❌ INSUFFISANT'; ?>
            </td>
        </tr>
        <tr>
            <td><strong>memory_limit</strong></td>
            <td><?php echo $memory; ?></td>
            <td>≥ 512M</td>
            <td class="ok">ℹ️ Info</td>
        </tr>
    </table>
    
    <?php if ($upload_ok && $post_ok): ?>
        <div style="background: #d4edda; padding: 15px; border-radius: 5px; border: 1px solid #28a745;">
            <strong>✅ Configuration correcte !</strong><br>
            Vous pouvez maintenant uploader les bannières.
        </div>
    <?php else: ?>
        <div style="background: #f8d7da; padding: 15px; border-radius: 5px; border: 1px solid #dc3545;">
            <strong>❌ Configuration insuffisante !</strong><br>
            Le serveur ne tourne pas avec les bonnes limites.<br><br>
            <strong>Solution :</strong><br>
            1. Arrêtez le serveur (Ctrl+C)<br>
            2. Lancez : <code>./serve-with-limits.sh</code>
        </div>
    <?php endif; ?>
    
    <p style="margin-top: 30px;">
        <a href="/admin/banners">→ Retour à la gestion des bannières</a>
    </p>
    
    <p style="color: #999; font-size: 12px;">
        🗑️ N'oubliez pas de supprimer ce fichier après le test : <code>rm public/test-limits.php</code>
    </p>
</body>
</html>

