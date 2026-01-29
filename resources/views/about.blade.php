@extends('layouts.app')

@section('title', 'À propos de nous - Herime Académie')
@section('description', 'Découvrez Herime Académie : plateforme d\'apprentissage en ligne et espace de ressources professionnelles. Transformez vos compétences et développez votre carrière avec nous.')

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
                        <p class="mb-3"><strong>Herime Académie</strong> est une entité de l’entreprise <strong>Herime</strong> (<a href="https://www.herime.com" target="_blank" class="herime-link">www.herime.com</a>), un groupe pionnier du numérique en République Démocratique du Congo et en Afrique centrale.</p>
                        <p class="mb-3"><strong>Herime Académie est une plateforme d'apprentissage en ligne et un espace de ressources professionnelles</strong> qui révolutionne la façon dont les professionnels développent leurs compétences et accèdent aux outils nécessaires à leur réussite.</p>
                        <p class="mb-3">Notre plateforme intègre deux dimensions essentielles :</p>
                        <div class="row g-3 mb-3">
                            <div class="col-md-12">
                                <div class="p-3 bg-light rounded border-start border-4 border-primary">
                                    <h5 class="mb-2"><i class="fas fa-graduation-cap text-primary me-2"></i><strong>Plateforme d'apprentissage en ligne</strong></h5>
                                    <p class="mb-0 small">Des contenus structurés, certifiants et créés par des experts reconnus. Développez vos compétences à votre rythme, où que vous soyez, avec des contenus pédagogiques de qualité professionnelle.</p>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="p-3 bg-light rounded border-start border-4 border-warning">
                                    <h5 class="mb-2"><i class="fas fa-briefcase text-warning me-2"></i><strong>Espace de ressources professionnelles</strong></h5>
                                    <p class="mb-0 small">Un hub complet de ressources professionnelles : modèles de documents, templates, guides pratiques, outils d'analyse, frameworks éprouvés. Accédez à tout ce dont vous avez besoin pour exceller dans votre domaine professionnel.</p>
                                </div>
                            </div>
                        </div>
                        <p class="mb-0">Depuis notre création, nous nous engageons à démocratiser l'accès à l'éducation de qualité et aux ressources professionnelles premium, en proposant des contenus accessibles, innovants et adaptés aux réalités locales africaines.</p>
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
                        <p class="mb-3">Notre mission est de rendre l'éducation de qualité et les ressources professionnelles premium accessibles à tous, partout et à tout moment. Nous croyons fermement que l'apprentissage et le développement professionnel ne devraient pas être limités par des contraintes géographiques, financières ou temporelles.</p>
                        <p class="mb-2"><strong>Chez Herime Académie, nous nous engageons à :</strong></p>
                        <ul class="list-unstyled">
                            <li class="mb-2"><i class="fas fa-check-circle text-primary me-2"></i>Offrir des contenus en ligne de haute qualité dispensés par des experts reconnus</li>
                            <li class="mb-2"><i class="fas fa-check-circle text-primary me-2"></i>Fournir un accès à des ressources professionnelles premium (modèles, templates, outils, frameworks)</li>
                            <li class="mb-2"><i class="fas fa-check-circle text-primary me-2"></i>Favoriser l'autonomie et la flexibilité dans l'apprentissage et le développement professionnel</li>
                            <li class="mb-2"><i class="fas fa-check-circle text-primary me-2"></i>Construire une communauté active d'apprenants et de professionnels passionnés</li>
                            <li class="mb-0"><i class="fas fa-check-circle text-primary me-2"></i>Permettre le développement personnel et professionnel de tous nos utilisateurs</li>
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
                        <p class="mb-3">Devenir la référence de l'éducation en ligne et des ressources professionnelles en Afrique centrale et francophone, en proposant des contenus pédagogiques innovants et des outils pratiques pertinents et directement applicables dans le contexte local africain.</p>
                        <p class="mb-2"><strong>Nous aspirons à :</strong></p>
                        <ul class="list-unstyled">
                            <li class="mb-2"><i class="fas fa-check-circle text-primary me-2"></i>Être la plateforme de choix pour les professionnels en quête de développement de compétences et d'accès à des ressources premium</li>
                            <li class="mb-2"><i class="fas fa-check-circle text-primary me-2"></i>Transformer la façon dont les Congolais et les Africains abordent l'éducation et le développement professionnel</li>
                            <li class="mb-2"><i class="fas fa-check-circle text-primary me-2"></i>Contribuer au développement économique local et régional par le contenu et l'accès aux ressources professionnelles</li>
                            <li class="mb-0"><i class="fas fa-check-circle text-primary me-2"></i>Établir un réseau dynamique d'experts, de professionnels et d'apprenants à travers toute l'Afrique</li>
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
                        <p class="card-text small text-muted">Nous nous efforçons de maintenir les plus hauts standards de qualité dans tous nos contenus, ressources professionnelles et dans chaque interaction avec nos utilisateurs.</p>
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
                        <p class="card-text small text-muted">L'éducation et les ressources professionnelles doivent être accessibles à tous, indépendamment de leur localisation, de leur situation financière ou de leur emploi du temps.</p>
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
                        <p class="card-text small text-muted">Nous adoptons les dernières technologies et méthodes pédagogiques pour offrir une expérience d'apprentissage exceptionnelle et développer des ressources professionnelles innovantes.</p>
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
                        <p class="card-text small text-muted">Nous construisons une communauté solidaire et engagée d'apprenants et de professionnels qui s'entraident et progressent ensemble.</p>
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
                        <p class="card-text small text-muted">Nous agissons toujours avec honnêteté, transparence et respect dans nos relations avec nos utilisateurs, étudiants, clients et partenaires.</p>
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
                        <p class="mb-3">Herime Académie est la plateforme qui combine <strong>apprentissage en ligne</strong> et <strong>ressources professionnelles</strong> pour votre succès complet.</p>
                        <div class="row g-3 mt-3">
                            <div class="col-md-6">
                                <h5 class="text-primary mb-3"><i class="fas fa-graduation-cap me-2"></i>Plateforme d'apprentissage en ligne</h5>
                                <ul class="list-unstyled">
                                    <li class="mb-3"><i class="fas fa-check-circle text-primary me-2"></i><strong>Contenus de qualité :</strong> Nos cours sont conçus et dispensés par des experts reconnus dans leurs domaines respectifs</li>
                                    <li class="mb-3"><i class="fas fa-check-circle text-primary me-2"></i><strong>Flexibilité totale :</strong> Apprenez à votre rythme, quand et où vous voulez</li>
                                    <li class="mb-3"><i class="fas fa-check-circle text-primary me-2"></i><strong>Accès illimité :</strong> Une fois acquis, votre contenu reste accessible à vie</li>
                                    <li class="mb-3"><i class="fas fa-check-circle text-primary me-2"></i><strong>Certificats reconnus :</strong> Obtenez des certifications valorisées sur le marché du travail</li>
                                    <li class="mb-0"><i class="fas fa-check-circle text-primary me-2"></i><strong>Parcours structurés :</strong> Suivez des parcours pédagogiques progressifs et complets</li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <h5 class="text-warning mb-3"><i class="fas fa-briefcase me-2"></i>Espace ressources professionnelles</h5>
                                <ul class="list-unstyled">
                                    <li class="mb-3"><i class="fas fa-check-circle text-warning me-2"></i><strong>Ressources premium :</strong> Accédez à des modèles, templates, guides et outils professionnels</li>
                                    <li class="mb-3"><i class="fas fa-check-circle text-warning me-2"></i><strong>Outils pratiques :</strong> Utilisez des frameworks éprouvés et des outils d'analyse professionnels</li>
                                    <li class="mb-3"><i class="fas fa-check-circle text-warning me-2"></i><strong>Bibliothèque complète :</strong> Une collection exhaustive de ressources pour tous les domaines</li>
                                    <li class="mb-0"><i class="fas fa-check-circle text-warning me-2"></i><strong>Mises à jour régulières :</strong> Des ressources constamment enrichies et actualisées</li>
                                </ul>
                            </div>
                            <div class="col-md-12">
                                <h5 class="text-primary mb-3"><i class="fas fa-star me-2"></i>Avantages transversaux</h5>
                                <div class="row">
                                    <div class="col-md-6">
                                        <ul class="list-unstyled">
                                            <li class="mb-2"><i class="fas fa-check-circle text-primary me-2"></i><strong>Prix compétitifs :</strong> Des contenus et ressources de qualité à des prix abordables</li>
                                            <li class="mb-0"><i class="fas fa-check-circle text-primary me-2"></i><strong>Méthodes de paiement flexibles :</strong> Paiement en ligne sécurisé ou via Mobile Money</li>
                                        </ul>
                                    </div>
                                    <div class="col-md-6">
                                        <ul class="list-unstyled">
                                            <li class="mb-2"><i class="fas fa-check-circle text-primary me-2"></i><strong>Communauté active :</strong> Rejoignez des milliers de professionnels passionnés</li>
                                            <li class="mb-0"><i class="fas fa-check-circle text-primary me-2"></i><strong>Contenu local :</strong> Des ressources adaptées au contexte africain et congolais</li>
                                        </ul>
                                    </div>
                                </div>
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
                            <p class="small text-muted mb-2">Propriété de l'entreprise Herime (<a href="https://www.herime.com" target="_blank" class="herime-link">www.herime.com</a>)</p>
                        </div>
                        <div class="col-md-6">
                            <p class="mb-1"><i class="fas fa-envelope me-2"></i>academie@herime.com</p>
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

/* Style pour les liens Herime */
a.herime-link {
    color: #0066cc !important;
    text-decoration: underline !important;
    font-weight: 500;
    transition: color 0.2s ease;
}

a.herime-link:hover {
    color: #0052a3 !important;
    text-decoration: underline !important;
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
