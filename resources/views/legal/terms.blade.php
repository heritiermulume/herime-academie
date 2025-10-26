@extends('layouts.app')

@section('title', 'Conditions Générales de Vente - Herime Académie')
@section('description', 'Consultez nos conditions générales de vente pour comprendre vos droits et obligations lors de l\'achat de cours sur Herime Académie.')

@section('content')
<div class="legal-page">
    <div class="legal-header">
        <div class="legal-wrapper">
            <h1 class="legal-title">Conditions Générales de Vente</h1>
            <p class="legal-subtitle">Dernière mise à jour : {{ date('d/m/Y') }}</p>
        </div>
    </div>

    <div class="legal-wrapper">
        <div class="legal-content">
            <section class="legal-section">
                <h2 class="section-title">
                    <i class="fas fa-book"></i>
                    1. Objet
                </h2>
                <p>Les présentes Conditions Générales de Vente (CGV) ont pour objet de définir les modalités et conditions de vente des formations proposées sur le site Herime Académie.</p>
                <p><strong>Herime Académie</strong> appartient à l'entreprise <strong>Herime</strong> (<a href="https://www.herime.com" target="_blank">www.herime.com</a>).</p>
                <p>Elles s'appliquent à tous les achats de formations effectués sur le site <strong>herimeacademie.com</strong>.</p>
            </section>

            <section class="legal-section">
                <h2 class="section-title">
                    <i class="fas fa-user"></i>
                    2. Acceptation des CGV
                </h2>
                <p>L'achat de toute formation sur le site implique l'acceptation pleine et entière par l'acheteur des présentes Conditions Générales de Vente.</p>
                <p>Herime Académie se réserve le droit de modifier les présentes CGV à tout moment. Les CGV applicables sont celles en vigueur au jour de l'achat.</p>
            </section>

            <section class="legal-section">
                <h2 class="section-title">
                    <i class="fas fa-shopping-cart"></i>
                    3. Produits et Services
                </h2>
                <p>Herime Académie propose des formations en ligne accessibles via la plateforme. Chaque formation comprend :</p>
                <ul>
                    <li>Un accès illimité au contenu de la formation</li>
                    <li>Des ressources téléchargeables (si disponibles)</li>
                    <li>Un certificat de complétion (pour les formations éligibles)</li>
                    <li>Un support technique et pédagogique</li>
                </ul>
            </section>

            <section class="legal-section">
                <h2 class="section-title">
                    <i class="fas fa-credit-card"></i>
                    4. Prix et Paiement
                </h2>
                <p>Les prix des formations sont indiqués en dollars américains (USD) et sont valables tant qu'ils sont visibles sur le site.</p>
                <p>Les moyens de paiement acceptés sont :</p>
                <ul>
                    <li>MaxiCash (Mobile Money, Cartes bancaires, PayPal)</li>
                    <li>Commande via WhatsApp (paiement manuel)</li>
                </ul>
                <p>Le paiement s'effectue de manière sécurisée via notre partenaire de paiement.</p>
            </section>

            <section class="legal-section">
                <h2 class="section-title">
                    <i class="fas fa-key"></i>
                    5. Accès aux Formations
                </h2>
                <p>Après validation du paiement, l'acheteur reçoit par email ses identifiants de connexion pour accéder à la formation.</p>
                <p>L'accès est valable à vie. L'acheteur peut suivre la formation à son rythme, sans limitation de temps.</p>
            </section>

            <section class="legal-section">
                <h2 class="section-title">
                    <i class="fas fa-undo"></i>
                    6. Droit de Rétractation
                </h2>
                <p>Conformément à la législation en vigueur, l'acheteur dispose d'un délai de 14 jours calendaires pour exercer son droit de rétractation.</p>
                <p>La rétractation doit être exercée <strong>avant le début de la formation</strong> ou dans les 48 heures suivant l'achat si la formation a déjà été entamée.</p>
                <p>Pour exercer ce droit, contactez-nous à : <strong>contact@herime.com</strong></p>
            </section>

            <section class="legal-section">
                <h2 class="section-title">
                    <i class="fas fa-ban"></i>
                    7. Propriété Intellectuelle
                </h2>
                <p>Tous les contenus proposés sur Herime Académie (vidéos, documents, textes, images) sont protégés par le droit de la propriété intellectuelle.</p>
                <p>Toute reproduction, représentation, modification ou exploitation non autorisée des contenus est interdite et peut entraîner des poursuites judiciaires.</p>
                <p>L'accès à une formation est strictement personnel et ne peut être cédé à un tiers.</p>
            </section>

            <section class="legal-section">
                <h2 class="section-title">
                    <i class="fas fa-headset"></i>
                    8. Support et Assistance
                </h2>
                <p>Herime Académie s'engage à fournir un support technique et pédagogique à tous les utilisateurs.</p>
                <p>Pour toute question ou problème :</p>
                <ul>
                    <li>Email : contact@herime.com</li>
                    <li>Téléphone : +243 824 449 218</li>
                </ul>
            </section>

            <section class="legal-section">
                <h2 class="section-title">
                    <i class="fas fa-shield-alt"></i>
                    9. Responsabilité
                </h2>
                <p>Herime Académie fait ses meilleurs efforts pour assurer la disponibilité et la qualité des formations proposées.</p>
                <p>Herime Académie ne saurait être tenu responsable :</p>
                <ul>
                    <li>Des dommages directs ou indirects résultant de l'utilisation ou de l'impossibilité d'utiliser le site</li>
                    <li>Des interruptions de service</li>
                    <li>De la perte de données</li>
                </ul>
            </section>

            <section class="legal-section">
                <h2 class="section-title">
                    <i class="fas fa-gavel"></i>
                    10. Droit Applicable et Juridiction
                </h2>
                <p>Les présentes CGV sont régies par le droit de la République Démocratique du Congo.</p>
                <p>Tout litige relatif à l'interprétation ou à l'exécution des présentes CGV sera de la compétence exclusive des tribunaux de Kinshasa.</p>
            </section>

            <section class="legal-section">
                <h2 class="section-title">
                    <i class="fas fa-phone"></i>
                    Contact
                </h2>
                <p>Pour toute question relative aux présentes Conditions Générales de Vente :</p>
                <div class="contact-info">
                    <p><strong>Herime Académie</strong></p>
                    <p><small class="text-muted">Propriété de l'entreprise Herime (<a href="https://www.herime.com" target="_blank">www.herime.com</a>)</small></p>
                    <p><i class="fas fa-envelope me-2"></i>contact@herime.com</p>
                    <p><i class="fas fa-phone me-2"></i>+243 824 449 218</p>
                </div>
            </section>
        </div>
    </div>
</div>

@push('styles')
<style>
.legal-page {
    background-color: #f7f9fa;
    min-height: 100vh;
}

.legal-header {
    background-color: #003366;
    color: white;
    padding: 60px 0;
    margin-bottom: 50px;
}

.legal-wrapper {
    max-width: 900px;
    margin: 0 auto;
    padding: 0 24px;
}

.legal-title {
    font-size: 2.5rem;
    font-weight: 700;
    margin-bottom: 10px;
}

.legal-subtitle {
    font-size: 1rem;
    opacity: 0.9;
    margin: 0;
}

.legal-content {
    background: white;
    border-radius: 12px;
    padding: 40px;
    box-shadow: 0 2px 15px rgba(0,0,0,0.1);
    margin-bottom: 50px;
}

.legal-section {
    margin-bottom: 40px;
}

.legal-section:last-child {
    margin-bottom: 0;
}

.section-title {
    color: #003366;
    font-size: 1.5rem;
    font-weight: 700;
    border-bottom: 3px solid #ffcc33;
    padding-bottom: 15px;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 12px;
}

.section-title i {
    color: #ffcc33;
}

.legal-content ul {
    margin: 15px 0;
    padding-left: 30px;
}

.legal-content li {
    margin-bottom: 10px;
    line-height: 1.8;
}

.contact-info {
    background: #f8f9fa;
    border-left: 4px solid #003366;
    padding: 20px;
    border-radius: 8px;
}

.contact-info p {
    margin-bottom: 10px;
    display: flex;
    align-items: center;
}

.contact-info i {
    color: #003366;
    width: 20px;
}

.contact-info a {
    color: #003366;
    text-decoration: underline;
    font-weight: 500;
}

.contact-info a:hover {
    color: #ffcc33;
}

.text-muted {
    color: #6c757d !important;
}

/* Responsive */
@media (max-width: 768px) {
    .legal-title {
        font-size: 2rem;
    }
    
    .legal-content {
        padding: 25px;
    }
    
    .section-title {
        font-size: 1.25rem;
    }
}

@media (max-width: 480px) {
    .legal-header {
        padding: 40px 0;
    }
    
    .legal-title {
        font-size: 1.75rem;
    }
    
    .legal-content {
        padding: 20px;
    }
    
    .section-title {
        font-size: 1.1rem;
    }
}
</style>
@endpush
@endsection

