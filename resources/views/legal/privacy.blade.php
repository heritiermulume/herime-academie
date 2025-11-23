@extends('layouts.app')

@section('title', 'Politique de Confidentialité - Herime Académie')
@section('description', 'Consultez notre politique de confidentialité pour comprendre comment nous collectons, utilisons et protégeons vos données personnelles.')

@section('content')
<!-- Page Header Section -->
<section class="page-header-section">
    <div class="container">
        <div class="row">
            <div class="col-12 text-center">
                <h1>Politique de Confidentialité</h1>
                <p class="lead">Dernière mise à jour : {{ date('d/m/Y') }}</p>
            </div>
        </div>
    </div>
</section>

<!-- Page Content Section -->
<section class="page-content-section">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body p-4 p-md-5">
                        <h2 class="section-title-modern">
                            <i class="fas fa-info-circle me-2"></i>
                            1. Introduction
                        </h2>
                        <p class="mb-3">Herime Académie s'engage à protéger et respecter votre vie privée. Cette politique de confidentialité explique comment nous collectons, utilisons et protégeons vos données personnelles conformément à la réglementation en vigueur.</p>
                        <p class="mb-0"><strong>Herime Académie</strong> appartient à l'entreprise <strong>Herime</strong> (<a href="https://www.herime.com" target="_blank">www.herime.com</a>).</p>
                    </div>
                </div>

                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body p-4 p-md-5">
                        <h2 class="section-title-modern">
                            <i class="fas fa-database me-2"></i>
                            2. Données Collectées
                        </h2>
                        <p class="mb-3">Nous collectons les données suivantes :</p>
                        
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="bg-light p-3 rounded">
                                    <h5 class="mb-2"><i class="fas fa-user me-2 text-primary"></i>2.1. Données d'identification</h5>
                                    <ul class="small mb-0">
                                        <li>Nom et prénom</li>
                                        <li>Adresse email</li>
                                        <li>Numéro de téléphone (optionnel)</li>
                                        <li>Adresse postale</li>
                                    </ul>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="bg-light p-3 rounded">
                                    <h5 class="mb-2"><i class="fas fa-network-wired me-2 text-primary"></i>2.2. Données de connexion</h5>
                                    <ul class="small mb-0">
                                        <li>Adresse IP</li>
                                        <li>Type de navigateur</li>
                                        <li>Cookies et données de navigation</li>
                                    </ul>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="bg-light p-3 rounded">
                                    <h5 class="mb-2"><i class="fas fa-credit-card me-2 text-primary"></i>2.3. Données de paiement</h5>
                                    <ul class="small mb-0">
                                        <li>Informations de facturation (traitées par nos partenaires de paiement sécurisés)</li>
                                        <li>Historique des transactions</li>
                                    </ul>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="bg-light p-3 rounded">
                                    <h5 class="mb-2"><i class="fas fa-chart-line me-2 text-primary"></i>2.4. Données d'utilisation</h5>
                                    <ul class="small mb-0">
                                        <li>Progress dans les formations</li>
                                        <li>Certificats obtenus</li>
                                        <li>Interactions avec les cours</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body p-4 p-md-5">
                        <h2 class="section-title-modern">
                            <i class="fas fa-bullseye me-2"></i>
                            3. Utilisation des Données
                        </h2>
                        <p class="mb-3">Vos données personnelles sont utilisées pour :</p>
                        <div class="row g-2">
                            <div class="col-md-6">
                                <ul class="list-unstyled">
                                    <li class="mb-2"><i class="fas fa-check-circle text-primary me-2"></i>Vous fournir nos services et gérer votre compte</li>
                                    <li class="mb-2"><i class="fas fa-check-circle text-primary me-2"></i>Traiter vos commandes et paiements</li>
                                    <li class="mb-2"><i class="fas fa-check-circle text-primary me-2"></i>Vous permettre d'accéder à vos formations</li>
                                    <li class="mb-0"><i class="fas fa-check-circle text-primary me-2"></i>Vous envoyer des informations sur nos services (avec votre consentement)</li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <ul class="list-unstyled">
                                    <li class="mb-2"><i class="fas fa-check-circle text-primary me-2"></i>Améliorer notre plateforme et nos services</li>
                                    <li class="mb-2"><i class="fas fa-check-circle text-primary me-2"></i>Assurer la sécurité de la plateforme</li>
                                    <li class="mb-0"><i class="fas fa-check-circle text-primary me-2"></i>Respecter nos obligations légales</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body p-4 p-md-5">
                        <h2 class="section-title-modern">
                            <i class="fas fa-key me-2"></i>
                            4. Protection des Données
                        </h2>
                        <p class="mb-3">Herime Académie met en œuvre des mesures techniques et organisationnelles appropriées pour protéger vos données personnelles :</p>
                        <div class="row g-2">
                            <div class="col-md-6">
                                <ul class="list-unstyled">
                                    <li class="mb-2"><i class="fas fa-shield-alt text-primary me-2"></i><strong>Chiffrement SSL/TLS</strong> pour toutes les communications</li>
                                    <li class="mb-0"><i class="fas fa-server text-primary me-2"></i><strong>Stockage sécurisé</strong> de vos données sur des serveurs hébergés en toute sécurité</li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <ul class="list-unstyled">
                                    <li class="mb-2"><i class="fas fa-lock text-primary me-2"></i><strong>Accès restreint</strong> aux données personnelles</li>
                                    <li class="mb-0"><i class="fas fa-key text-primary me-2"></i><strong>Mots de passe chiffrés</strong> pour la sécurité de votre compte</li>
                                </ul>
                            </div>
                        </div>
                        <div class="alert alert-info mt-3 mb-0">
                            <i class="fas fa-info-circle me-2"></i>
                            Nous ne partageons jamais vos données avec des tiers à des fins commerciales sans votre consentement explicite.
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body p-4 p-md-5">
                        <h2 class="section-title-modern">
                            <i class="fas fa-share-alt me-2"></i>
                            5. Partage de Données
                        </h2>
                        <p class="mb-3">Nous pouvons partager vos données avec :</p>
                        <ul class="list-unstyled">
                            <li class="mb-2"><i class="fas fa-building text-primary me-2"></i><strong>Prestataires de paiement</strong> (PayPal) pour le traitement des transactions</li>
                            <li class="mb-2"><i class="fas fa-cloud text-primary me-2"></i><strong>Hébergeurs et services cloud</strong> pour le fonctionnement de la plateforme</li>
                            <li class="mb-0"><i class="fas fa-gavel text-primary me-2"></i><strong>Autorités légales</strong> si la loi l'exige</li>
                        </ul>
                        <p class="mb-0 mt-3"><strong>Tous nos partenaires sont soumis à des obligations strictes de confidentialité.</strong></p>
                    </div>
                </div>

                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body p-4 p-md-5">
                        <h2 class="section-title-modern">
                            <i class="fas fa-cookie me-2"></i>
                            6. Cookies
                        </h2>
                        <p class="mb-3">Nous utilisons des cookies pour :</p>
                        <div class="row g-2">
                            <div class="col-md-6">
                                <ul class="list-unstyled">
                                    <li class="mb-2"><i class="fas fa-check-circle text-primary me-2"></i>Maintenir votre session de connexion</li>
                                    <li class="mb-0"><i class="fas fa-check-circle text-primary me-2"></i>Mémoriser vos préférences</li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <ul class="list-unstyled">
                                    <li class="mb-2"><i class="fas fa-check-circle text-primary me-2"></i>Améliorer votre expérience de navigation</li>
                                    <li class="mb-0"><i class="fas fa-check-circle text-primary me-2"></i>Analyser le trafic et l'utilisation du site</li>
                                </ul>
                            </div>
                        </div>
                        <p class="mb-0 mt-3">Vous pouvez gérer vos préférences de cookies dans les paramètres de votre navigateur.</p>
                    </div>
                </div>

                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body p-4 p-md-5">
                        <h2 class="section-title-modern">
                            <i class="fas fa-user-cog me-2"></i>
                            7. Vos Droits
                        </h2>
                        <p class="mb-3">Conformément à la réglementation, vous disposez des droits suivants :</p>
                        <div class="row g-2">
                            <div class="col-md-6">
                                <ul class="list-unstyled">
                                    <li class="mb-2"><i class="fas fa-eye text-primary me-2"></i><strong>Droit d'accès</strong> : Accéder à vos données personnelles</li>
                                    <li class="mb-2"><i class="fas fa-edit text-primary me-2"></i><strong>Droit de rectification</strong> : Corriger vos données inexactes</li>
                                    <li class="mb-0"><i class="fas fa-trash text-primary me-2"></i><strong>Droit à l'effacement</strong> : Demander la suppression de vos données</li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <ul class="list-unstyled">
                                    <li class="mb-2"><i class="fas fa-download text-primary me-2"></i><strong>Droit à la portabilité</strong> : Récupérer vos données dans un format lisible</li>
                                    <li class="mb-2"><i class="fas fa-ban text-primary me-2"></i><strong>Droit d'opposition</strong> : Vous opposer au traitement de vos données</li>
                                    <li class="mb-0"><i class="fas fa-times-circle text-primary me-2"></i><strong>Droit de retrait du consentement</strong> : Retirer votre consentement à tout moment</li>
                                </ul>
                            </div>
                        </div>
                        <div class="alert alert-primary mt-3 mb-0">
                            <i class="fas fa-envelope me-2"></i>
                            Pour exercer ces droits, contactez-nous à : <strong>contact@herime.com</strong>
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body p-4 p-md-5">
                        <h2 class="section-title-modern">
                            <i class="fas fa-clock me-2"></i>
                            8. Conservation des Données
                        </h2>
                        <div class="row g-2">
                            <div class="col-md-6">
                                <ul class="list-unstyled">
                                    <li class="mb-2"><strong>Données de compte :</strong> Tant que votre compte est actif</li>
                                    <li class="mb-0"><strong>Données de transaction :</strong> 10 ans (obligation légale)</li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <ul class="list-unstyled">
                                    <li class="mb-2"><strong>Données de navigation :</strong> 13 mois maximum</li>
                                    <li class="mb-0"><strong>Données supprimées :</strong> Conservation 30 jours en sauvegarde</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body p-4 p-md-5">
                        <h2 class="section-title-modern">
                            <i class="fas fa-baby me-2"></i>
                            9. Mineurs
                        </h2>
                        <p class="mb-2">Nos services sont destinés aux personnes majeures (18 ans et plus).</p>
                        <p class="mb-2">Si vous avez moins de 18 ans, vous devez obtenir l'autorisation de vos parents ou tuteurs légaux avant de créer un compte.</p>
                        <p class="mb-0">Nous ne collectons pas sciemment de données personnelles de mineurs sans autorisation parentale.</p>
                    </div>
                </div>

                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body p-4 p-md-5">
                        <h2 class="section-title-modern">
                            <i class="fas fa-sync me-2"></i>
                            10. Modifications de la Politique
                        </h2>
                        <p class="mb-2">Nous nous réservons le droit de modifier cette politique de confidentialité à tout moment.</p>
                        <p class="mb-2">Toute modification sera publiée sur cette page avec la date de mise à jour.</p>
                        <p class="mb-0">Nous vous recommandons de consulter régulièrement cette page.</p>
                    </div>
                </div>

                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4 p-md-5">
                        <h2 class="section-title-modern">
                            <i class="fas fa-phone me-2"></i>
                            Contact
                        </h2>
                        <p class="mb-3">Pour toute question concernant cette politique de confidentialité ou vos données personnelles :</p>
                        <div class="alert alert-primary mb-0">
                            <p class="mb-1"><strong>Herime Académie</strong></p>
                            <p class="small mb-2 text-muted">Propriété de l'entreprise Herime (<a href="https://www.herime.com" target="_blank" class="text-decoration-none">www.herime.com</a>)</p>
                            <p class="mb-1"><i class="fas fa-envelope me-2"></i>contact@herime.com</p>
                            <p class="mb-0"><i class="fas fa-phone me-2"></i>+243 824 449 218</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
