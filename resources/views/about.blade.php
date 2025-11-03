@extends('layouts.app')

@section('title', 'À propos de nous - Herime Académie')
@section('description', 'Découvrez l\'histoire, la mission et la vision de Herime Académie, la plateforme d\'apprentissage en ligne du Groupe Herime.')

@section('content')
<!-- Page Header Section -->
<section class="page-header-section">
    <div class="container">
        <div class="row">
            <div class="col-12 text-center">
                <h1>À propos de nous</h1>
                <p class="lead">Découvrez qui nous sommes</p>
            </div>
        </div>
    </div>
</section>

<!-- Page Content Section -->
<section class="page-content-section">
    <div class="container">
        <!-- Qui sommes-nous -->
        <div class="row mb-5">
            <div class="col-12">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body p-4 p-md-5">
                        <h2 class="section-title-modern">
                            <i class="fas fa-building me-2"></i>
                            Qui sommes-nous ?
                        </h2>
                        <p class="mb-3"><strong>Herime Académie</strong> appartient à l'entreprise <strong>Herime</strong> (<a href="https://www.herime.com" target="_blank">www.herime.com</a>), un groupe pionnier dans l'éducation numérique en République Démocratique du Congo.</p>
                        <p class="mb-0">Depuis notre création, nous nous engageons à démocratiser l'accès à l'éducation de qualité en proposant des formations en ligne accessibles, innovantes et adaptées aux besoins locaux.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Mission & Vision -->
        <div class="row g-4 mb-5">
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-4 p-md-5">
                        <h2 class="section-title-modern">
                            <i class="fas fa-bullseye me-2"></i>
                            Notre Mission
                        </h2>
                        <p class="mb-3">Notre mission est de rendre l'éducation de qualité accessible à tous, partout et à tout moment. Nous croyons fermement que l'apprentissage ne devrait pas être limité par des contraintes géographiques, financières ou temporelles.</p>
                        <p class="mb-2"><strong>Chez Herime Académie, nous nous engageons à :</strong></p>
                        <ul class="list-unstyled">
                            <li class="mb-2"><i class="fas fa-check-circle text-primary me-2"></i>Offrir des cours de haute qualité dispensés par des experts</li>
                            <li class="mb-2"><i class="fas fa-check-circle text-primary me-2"></i>Favoriser l'autonomie et la flexibilité dans l'apprentissage</li>
                            <li class="mb-2"><i class="fas fa-check-circle text-primary me-2"></i>Construire une communauté d'apprenants passionnés</li>
                            <li class="mb-0"><i class="fas fa-check-circle text-primary me-2"></i>Permettre le développement personnel et professionnel de nos étudiants</li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-4 p-md-5">
                        <h2 class="section-title-modern">
                            <i class="fas fa-eye me-2"></i>
                            Notre Vision
                        </h2>
                        <p class="mb-3">Devenir la référence de l'éducation en ligne en Afrique centrale et francophone, en proposant des contenus pédagogiques innovants, pertinents et directement applicables dans le contexte local.</p>
                        <p class="mb-2"><strong>Nous aspirons à :</strong></p>
                        <ul class="list-unstyled">
                            <li class="mb-2"><i class="fas fa-check-circle text-primary me-2"></i>Être la plateforme de choix pour les professionnels en quête de développement de compétences</li>
                            <li class="mb-2"><i class="fas fa-check-circle text-primary me-2"></i>Transformer la façon dont les Congolais et les Africains abordent l'éducation</li>
                            <li class="mb-2"><i class="fas fa-check-circle text-primary me-2"></i>Contribuer au développement économique local par la formation</li>
                            <li class="mb-0"><i class="fas fa-check-circle text-primary me-2"></i>Établir un réseau d'experts et d'apprenants à travers toute l'Afrique</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- Nos Valeurs -->
        <div class="row mb-5">
            <div class="col-12">
                <h2 class="section-title-modern text-center mb-4">
                    <i class="fas fa-heart me-2"></i>
                    Nos Valeurs
                </h2>
            </div>
        </div>
        <div class="row g-4 mb-5">
            <div class="col-md-6 col-lg-4">
                <div class="card border-0 shadow-sm h-100 value-card">
                    <div class="card-body p-4 text-center">
                        <div class="value-icon mb-3">
                            <i class="fas fa-graduation-cap"></i>
                        </div>
                        <h5 class="card-title">Excellence</h5>
                        <p class="card-text small text-muted">Nous nous efforçons de maintenir les plus hauts standards de qualité dans tous nos cours et dans chaque interaction avec nos étudiants.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-4">
                <div class="card border-0 shadow-sm h-100 value-card">
                    <div class="card-body p-4 text-center">
                        <div class="value-icon mb-3">
                            <i class="fas fa-unlock-alt"></i>
                        </div>
                        <h5 class="card-title">Accessibilité</h5>
                        <p class="card-text small text-muted">L'éducation doit être accessible à tous, indépendamment de leur localisation, de leur situation financière ou de leur emploi du temps.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-4">
                <div class="card border-0 shadow-sm h-100 value-card">
                    <div class="card-body p-4 text-center">
                        <div class="value-icon mb-3">
                            <i class="fas fa-lightbulb"></i>
                        </div>
                        <h5 class="card-title">Innovation</h5>
                        <p class="card-text small text-muted">Nous adoptons les dernières technologies et méthodes pédagogiques pour offrir une expérience d'apprentissage exceptionnelle.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-4">
                <div class="card border-0 shadow-sm h-100 value-card">
                    <div class="card-body p-4 text-center">
                        <div class="value-icon mb-3">
                            <i class="fas fa-users"></i>
                        </div>
                        <h5 class="card-title">Communauté</h5>
                        <p class="card-text small text-muted">Nous construisons une communauté solidaire et engagée d'apprenants qui s'entraident et progressent ensemble.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-4">
                <div class="card border-0 shadow-sm h-100 value-card">
                    <div class="card-body p-4 text-center">
                        <div class="value-icon mb-3">
                            <i class="fas fa-handshake"></i>
                        </div>
                        <h5 class="card-title">Intégrité</h5>
                        <p class="card-text small text-muted">Nous agissons toujours avec honnêteté, transparence et respect dans nos relations avec nos étudiants et partenaires.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-4">
                <div class="card border-0 shadow-sm h-100 value-card">
                    <div class="card-body p-4 text-center">
                        <div class="value-icon mb-3">
                            <i class="fas fa-rocket"></i>
                        </div>
                        <h5 class="card-title">Excellence Opérationnelle</h5>
                        <p class="card-text small text-muted">Nous optimisons continuellement nos processus pour offrir la meilleure expérience possible à nos utilisateurs.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pourquoi nous choisir -->
        <div class="row mb-5">
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4 p-md-5">
                        <h2 class="section-title-modern">
                            <i class="fas fa-award me-2"></i>
                            Pourquoi nous choisir ?
                        </h2>
                        <div class="row g-3 mt-3">
                            <div class="col-md-6">
                                <ul class="list-unstyled">
                                    <li class="mb-3"><i class="fas fa-check-circle text-primary me-2"></i><strong>Contenus de qualité :</strong> Nos cours sont conçus et dispensés par des experts reconnus dans leurs domaines respectifs</li>
                                    <li class="mb-3"><i class="fas fa-check-circle text-primary me-2"></i><strong>Flexibilité totale :</strong> Apprenez à votre rythme, quand et où vous voulez</li>
                                    <li class="mb-3"><i class="fas fa-check-circle text-primary me-2"></i><strong>Accès illimité :</strong> Une fois acquis, votre formation reste accessible à vie</li>
                                    <li class="mb-0"><i class="fas fa-check-circle text-primary me-2"></i><strong>Certificats reconnus :</strong> Obtenez des certifications valorisées sur le marché du travail</li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <ul class="list-unstyled">
                                    <li class="mb-3"><i class="fas fa-check-circle text-primary me-2"></i><strong>Support dédié :</strong> Une équipe à votre écoute pour vous accompagner dans votre parcours</li>
                                    <li class="mb-3"><i class="fas fa-check-circle text-primary me-2"></i><strong>Prix compétitifs :</strong> Des formations de qualité à des prix abordables</li>
                                    <li class="mb-0"><i class="fas fa-check-circle text-primary me-2"></i><strong>Méthodes de paiement flexibles :</strong> Paiement en ligne sécurisé ou via Mobile Money</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Contact -->
        <div class="row">
            <div class="col-12 col-lg-8 mx-auto">
                <div class="alert alert-primary border-0 shadow-sm">
                    <h5 class="alert-heading mb-3">
                        <i class="fas fa-phone me-2"></i>Contactez-nous
                    </h5>
                    <p class="mb-2">Pour toute question ou pour en savoir plus sur Herime Académie :</p>
                    <div class="row g-2">
                        <div class="col-12">
                            <p class="mb-1"><strong>Herime Académie</strong></p>
                            <p class="small text-muted mb-2">Propriété de l'entreprise Herime (<a href="https://www.herime.com" target="_blank" class="text-decoration-none">www.herime.com</a>)</p>
                        </div>
                        <div class="col-md-6">
                            <p class="mb-1"><i class="fas fa-envelope me-2"></i>contact@herime.com</p>
                        </div>
                        <div class="col-md-6">
                            <p class="mb-0"><i class="fas fa-phone me-2"></i>+243 824 449 218</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

@push('styles')
<style>
.value-card {
    transition: all 0.3s ease;
    border-radius: 16px;
}

.value-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 25px rgba(0, 51, 102, 0.15) !important;
}

.value-icon {
    width: 70px;
    height: 70px;
    background: linear-gradient(135deg, var(--primary-color) 0%, #004080 100%);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto;
    color: white;
    font-size: 2rem;
    box-shadow: 0 4px 15px rgba(0, 51, 102, 0.2);
}

.list-unstyled li {
    line-height: 1.8;
}

/* Responsive */
@media (max-width: 767.98px) {
    .value-icon {
        width: 60px;
        height: 60px;
        font-size: 1.75rem;
    }
}
</style>
@endpush
@endsection
