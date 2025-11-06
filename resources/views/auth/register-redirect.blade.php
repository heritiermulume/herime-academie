<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Inscription - Redirection vers le SSO</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
        }
        .redirect-container {
            background: white;
            border-radius: 20px;
            padding: 3rem;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            text-align: center;
            max-width: 500px;
            width: 90%;
        }
        .spinner-border {
            width: 3rem;
            height: 3rem;
            border-width: 0.3rem;
        }
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            padding: 0.75rem 2rem;
            font-weight: 600;
            border-radius: 10px;
            transition: transform 0.2s;
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.4);
        }
    </style>
</head>
<body>
    <div class="redirect-container">
        <div class="mb-4">
            <i class="fas fa-user-plus fa-3x text-primary mb-3"></i>
            <h2 class="mb-3">Inscription</h2>
            <p class="text-muted mb-4">
                Vous allez être redirigé vers notre page d'inscription dans un nouvel onglet.
            </p>
        </div>
        
        <div class="mb-4">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Chargement...</span>
            </div>
        </div>
        
        <p class="text-muted small mb-4">
            Si la nouvelle fenêtre ne s'ouvre pas automatiquement, cliquez sur le bouton ci-dessous.
        </p>
        
        <button onclick="openRegisterWindow()" class="btn btn-primary btn-lg">
            <i class="fas fa-external-link-alt me-2"></i>
            Ouvrir la page d'inscription
        </button>
        
        <div class="mt-4">
            <a href="{{ route('home') }}" class="text-muted text-decoration-none">
                <i class="fas fa-arrow-left me-1"></i>
                Retour à l'accueil
            </a>
        </div>
    </div>

    <script>
        function openRegisterWindow() {
            const ssoRegisterUrl = @json($ssoRegisterUrl);
            window.open(ssoRegisterUrl, '_blank', 'noopener,noreferrer');
        }

        // Ouvrir automatiquement dans un nouvel onglet au chargement de la page
        window.addEventListener('load', function() {
            openRegisterWindow();
        });
    </script>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

