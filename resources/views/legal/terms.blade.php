@extends('layouts.app')

@section('title', 'Conditions Générales de Vente - Herime Académie')
@section('description', 'Consultez nos conditions générales de vente pour comprendre vos droits et obligations lors de l\'achat de cours sur Herime Académie.')

@section('content')
<!-- Page Header Section -->
<section class="page-header-section">
    <div class="container">
        <div class="row">
            <div class="col-12 text-center">
                <h1>Conditions Générales de Vente</h1>
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
                            <i class="fas fa-book me-2"></i>
                            1. Objet
                        </h2>
                        <p class="mb-2">Les présentes Conditions Générales de Vente (CGV) ont pour objet de définir les modalités et conditions de vente des formations proposées sur le site Herime Académie.</p>
                        <p class="mb-2"><strong>Herime Académie</strong> appartient à l'entreprise <strong>Herime</strong> (<a href="https://www.herime.com" target="_blank">www.herime.com</a>).</p>
                        <p class="mb-0">Elles s'appliquent à tous les achats de formations effectués sur le site <strong>herimeacademie.com</strong>.</p>
                    </div>
                </div>

                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body p-4 p-md-5">
                        <h2 class="section-title-modern">
                            <i class="fas fa-user me-2"></i>
                            2. Acceptation des CGV
                        </h2>
                        <p class="mb-2">L'achat de toute formation sur le site implique l'acceptation pleine et entière par l'acheteur des présentes Conditions Générales de Vente.</p>
                        <p class="mb-0">Herime Académie se réserve le droit de modifier les présentes CGV à tout moment. Les CGV applicables sont celles en vigueur au jour de l'achat.</p>
                    </div>
                </div>

                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body p-4 p-md-5">
                        <h2 class="section-title-modern">
                            <i class="fas fa-shopping-cart me-2"></i>
                            3. Produits et Services
                        </h2>
                        <p class="mb-3">Herime Académie propose des formations en ligne accessibles via la plateforme. Chaque formation comprend :</p>
                        <div class="row g-2">
                            <div class="col-md-6">
                                <ul class="list-unstyled">
                                    <li class="mb-2"><i class="fas fa-check-circle text-primary me-2"></i>Un accès illimité au contenu de la formation</li>
                                    <li class="mb-0"><i class="fas fa-check-circle text-primary me-2"></i>Des ressources téléchargeables (si disponibles)</li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <ul class="list-unstyled">
                                    <li class="mb-2"><i class="fas fa-check-circle text-primary me-2"></i>Un certificat de complétion (pour les formations éligibles)</li>
                                    <li class="mb-0"><i class="fas fa-check-circle text-primary me-2"></i>Un support technique et pédagogique</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body p-4 p-md-5">
                        <h2 class="section-title-modern">
                            <i class="fas fa-credit-card me-2"></i>
                            4. Prix et Paiement
                        </h2>
                        <p class="mb-3">Les prix des formations sont indiqués en dollars américains (USD) et sont valables tant qu'ils sont visibles sur le site.</p>
                        <p class="mb-2"><strong>Les moyens de paiement acceptés sont :</strong></p>
                        <ul class="list-unstyled">
                            <li class="mb-2"><i class="fas fa-mobile-alt text-primary me-2"></i>MaxiCash (Mobile Money, Cartes bancaires, PayPal)</li>
                            <li class="mb-0"><i class="fab fa-whatsapp text-primary me-2"></i>Commande via WhatsApp (paiement manuel)</li>
                        </ul>
                        <p class="mb-0 mt-3">Le paiement s'effectue de manière sécurisée via notre partenaire de paiement.</p>
                    </div>
                </div>

                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body p-4 p-md-5">
                        <h2 class="section-title-modern">
                            <i class="fas fa-key me-2"></i>
                            5. Accès aux Formations
                        </h2>
                        <p class="mb-2">Après validation du paiement, l'acheteur reçoit par email ses identifiants de connexion pour accéder à la formation.</p>
                        <p class="mb-0">L'accès est valable à vie. L'acheteur peut suivre la formation à son rythme, sans limitation de temps.</p>
                    </div>
                </div>

                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body p-4 p-md-5">
                        <h2 class="section-title-modern">
                            <i class="fas fa-undo me-2"></i>
                            6. Droit de Rétractation
                        </h2>
                        <p class="mb-2">Conformément à la législation en vigueur, l'acheteur dispose d'un délai de 14 jours calendaires pour exercer son droit de rétractation.</p>
                        <p class="mb-2">La rétractation doit être exercée <strong>avant le début de la formation</strong> ou dans les 48 heures suivant l'achat si la formation a déjà été entamée.</p>
                        <div class="alert alert-warning mt-3 mb-0">
                            <i class="fas fa-envelope me-2"></i>
                            Pour exercer ce droit, contactez-nous à : <strong>contact@herime.com</strong>
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body p-4 p-md-5">
                        <h2 class="section-title-modern">
                            <i class="fas fa-ban me-2"></i>
                            7. Propriété Intellectuelle
                        </h2>
                        <p class="mb-2">Tous les contenus proposés sur Herime Académie (vidéos, documents, textes, images) sont protégés par le droit de la propriété intellectuelle.</p>
                        <p class="mb-2">Toute reproduction, représentation, modification ou exploitation non autorisée des contenus est interdite et peut entraîner des poursuites judiciaires.</p>
                        <p class="mb-0">L'accès à une formation est strictement personnel et ne peut être cédé à un tiers.</p>
                    </div>
                </div>

                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body p-4 p-md-5">
                        <h2 class="section-title-modern">
                            <i class="fas fa-headset me-2"></i>
                            8. Support et Assistance
                        </h2>
                        <p class="mb-3">Herime Académie s'engage à fournir un support technique et pédagogique à tous les utilisateurs.</p>
                        <p class="mb-2"><strong>Pour toute question ou problème :</strong></p>
                        <ul class="list-unstyled">
                            <li class="mb-2"><i class="fas fa-envelope text-primary me-2"></i>Email : contact@herime.com</li>
                            <li class="mb-0"><i class="fas fa-phone text-primary me-2"></i>Téléphone : +243 824 449 218</li>
                        </ul>
                    </div>
                </div>

                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body p-4 p-md-5">
                        <h2 class="section-title-modern">
                            <i class="fas fa-shield-alt me-2"></i>
                            9. Responsabilité
                        </h2>
                        <p class="mb-3">Herime Académie fait ses meilleurs efforts pour assurer la disponibilité et la qualité des formations proposées.</p>
                        <p class="mb-2"><strong>Herime Académie ne saurait être tenu responsable :</strong></p>
                        <ul class="list-unstyled">
                            <li class="mb-2"><i class="fas fa-exclamation-triangle text-primary me-2"></i>Des dommages directs ou indirects résultant de l'utilisation ou de l'impossibilité d'utiliser le site</li>
                            <li class="mb-2"><i class="fas fa-exclamation-triangle text-primary me-2"></i>Des interruptions de service</li>
                            <li class="mb-0"><i class="fas fa-exclamation-triangle text-primary me-2"></i>De la perte de données</li>
                        </ul>
                    </div>
                </div>

                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body p-4 p-md-5">
                        <h2 class="section-title-modern">
                            <i class="fas fa-gavel me-2"></i>
                            10. Droit Applicable et Juridiction
                        </h2>
                        <p class="mb-2">Les présentes CGV sont régies par le droit de la République Démocratique du Congo.</p>
                        <p class="mb-0">Tout litige relatif à l'interprétation ou à l'exécution des présentes CGV sera de la compétence exclusive des tribunaux de Kinshasa.</p>
                    </div>
                </div>

                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4 p-md-5">
                        <h2 class="section-title-modern">
                            <i class="fas fa-phone me-2"></i>
                            Contact
                        </h2>
                        <p class="mb-3">Pour toute question relative aux présentes Conditions Générales de Vente :</p>
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
