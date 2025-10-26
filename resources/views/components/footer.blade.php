<!-- Footer Component -->
<footer class="footer">
    <div class="container">
        <div class="row">
            <div class="col-lg-4 col-md-6 mb-4 text-center text-lg-start">
                <img src="{{ asset('images/logo-herime-academie-blanc.png') }}" alt="Herime Academie" class="footer-logo">
                <p class="mb-3">Votre plateforme d'apprentissage en ligne de confiance. Découvrez des milliers de cours de qualité et développez vos compétences.</p>
                <div class="d-flex flex-wrap gap-3 justify-content-center justify-content-lg-start">
                    <a href="https://www.linkedin.com/company/herime1" target="_blank" class="text-white" title="LinkedIn">
                        <i class="fab fa-linkedin fa-lg"></i>
                    </a>
                    <a href="https://www.instagram.com/herime_1" target="_blank" class="text-white" title="Instagram">
                        <i class="fab fa-instagram fa-lg"></i>
                    </a>
                    <a href="https://www.facebook.com/herime1" target="_blank" class="text-white" title="Facebook">
                        <i class="fab fa-facebook fa-lg"></i>
                    </a>
                    <a href="https://www.tiktok.com/@herime_1" target="_blank" class="text-white" title="TikTok">
                        <i class="fab fa-tiktok fa-lg"></i>
                    </a>
                    <a href="https://www.youtube.com/@herime_1" target="_blank" class="text-white" title="YouTube">
                        <i class="fab fa-youtube fa-lg"></i>
                    </a>
                </div>
            </div>
            
            <div class="col-lg-2 col-md-6 col-sm-6 mb-4 text-center text-lg-start">
                <h5>Liens rapides</h5>
                <ul class="list-unstyled">
                    <li class="mb-2"><a href="{{ route('home') }}">Accueil</a></li>
                    <li class="mb-2"><a href="{{ route('courses.index') }}">Cours</a></li>
                    <li class="mb-2"><a href="{{ route('instructors.index') }}">Formateurs</a></li>
                    <li class="mb-2"><a href="{{ route('about') }}">À propos</a></li>
                    <li class="mb-2"><a href="{{ route('contact') }}">Contact</a></li>
                </ul>
            </div>
            
            <div class="col-lg-2 col-md-6 col-sm-6 mb-4 text-center text-lg-start">
                <h5>Catégories</h5>
                <ul class="list-unstyled">
                    @foreach(\App\Models\Category::active()->ordered()->limit(5)->get() as $category)
                        <li class="mb-2"><a href="{{ route('courses.category', $category->slug) }}">{{ $category->name }}</a></li>
                    @endforeach
                </ul>
            </div>
            
            <div class="col-lg-4 col-md-12 mb-4 text-center text-lg-start">
                <h5>Contact</h5>
                <div class="mb-3">
                    <i class="fas fa-phone me-2"></i>
                    <a href="tel:+243824449218">+243 824 449 218</a>
                </div>
                <div class="mb-3">
                    <i class="fas fa-map-marker-alt me-2"></i>
                    <span>25, Croisement Gambela et Lukandu,<br>Commune de Kasavubu, Kinshasa, RDC</span>
                </div>
                <div class="mb-3">
                    <i class="fab fa-whatsapp me-2"></i>
                    <a href="https://whatsapp.com/channel/0029VaU6teH3mFYCdZPjoT0h" target="_blank">Chaîne WhatsApp</a>
                </div>
            </div>
        </div>
        
        <hr class="my-4">
        
        <div class="row align-items-center">
            <div class="col-md-6 text-center text-md-start">
                <p class="mb-0">&copy; {{ date('Y') }} Herime Academie. Tous droits réservés.</p>
            </div>
            <div class="col-md-6 text-center text-md-end">
                <div class="d-flex flex-wrap gap-3 justify-content-center justify-content-md-end">
                    <a href="{{ route('legal.terms') }}" class="me-3">Conditions de vente</a>
                    <a href="{{ route('legal.privacy') }}" class="me-3">Politique de confidentialité</a>
                    <a href="{{ route('contact') }}">Contact</a>
                </div>
            </div>
        </div>
    </div>
</footer>
