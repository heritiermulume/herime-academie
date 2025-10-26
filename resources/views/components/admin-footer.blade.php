<!-- Admin Footer Component -->
<footer class="footer bg-dark text-light py-4 mt-5">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-6">
                <p class="mb-0">&copy; {{ date('Y') }} Herime Academie - Administration. Tous droits réservés.</p>
            </div>
            <div class="col-md-6 text-md-end">
                <small class="text-muted">Version {{ config('app.version', '1.0.0') }} | 
                    <a href="{{ route('home') }}" class="text-light">Retour au site</a>
                </small>
            </div>
        </div>
    </div>
</footer>
