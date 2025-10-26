@extends('layouts.app')

@section('title', 'Politique de Confidentialité - Herime Académie')
@section('description', 'Consultez notre politique de confidentialité pour comprendre comment nous collectons, utilisons et protégeons vos données personnelles.')

@section('content')
<div class="legal-page">
    <div class="legal-header">
        <div class="legal-wrapper">
            <h1 class="legal-title">Politique de Confidentialité</h1>
            <p class="legal-subtitle">Dernière mise à jour : {{ date('d/m/Y') }}</p>
        </div>
    </div>

    <div class="legal-wrapper">
        <div class="legal-content">
            <section class="legal-section">
                <h2 class="section-title">
                    <i class="fas fa-info-circle"></i>
                    1. Introduction
                </h2>
                <p>Herime Académie s'engage à protéger et respecter votre vie privée. Cette politique de confidentialité explique comment nous collectons, utilisons et protégeons vos données personnelles conformément à la réglementation en vigueur.</p>
                <p><strong>Herime Académie</strong> appartient à l'entreprise <strong>Herime</strong> (<a href="https://www.herime.com" target="_blank">www.herime.com</a>).</p>
            </section>

            <section class="legal-section">
                <h2 class="section-title">
                    <i class="fas fa-database"></i>
                    2. Données Collectées
                </h2>
                <p>Nous collectons les données suivantes :</p>
                <h3>2.1. Données d'identification</h3>
                <ul>
                    <li>Nom et prénom</li>
                    <li>Adresse email</li>
                    <li>Numéro de téléphone (optionnel)</li>
                    <li>Adresse postale</li>
                </ul>
                
                <h3>2.2. Données de connexion</h3>
                <ul>
                    <li>Adresse IP</li>
                    <li>Type de navigateur</li>
                    <li>Cookies et données de navigation</li>
                </ul>
                
                <h3>2.3. Données de paiement</h3>
                <ul>
                    <li>Informations de facturation (traitées par nos partenaires de paiement sécurisés)</li>
                    <li>Historique des transactions</li>
                </ul>
                
                <h3>2.4. Données d'utilisation</h3>
                <ul>
                    <li>Progress dans les formations</li>
                    <li>Certificats obtenus</li>
                    <li>Interactions avec les cours</li>
                </ul>
            </section>

            <section class="legal-section">
                <h2 class="section-title">
                    <i class="fas fa-bullseye"></i>
                    3. Utilisation des Données
                </h2>
                <p>Vos données personnelles sont utilisées pour :</p>
                <ul>
                    <li>Vous fournir nos services et gérer votre compte</li>
                    <li>Traiter vos commandes et paiements</li>
                    <li>Vous permettre d'accéder à vos formations</li>
                    <li>Vous envoyer des informations sur nos services (avec votre consentement)</li>
                    <li>Améliorer notre plateforme et nos services</li>
                    <li>Assurer la sécurité de la plateforme</li>
                    <li>Respecter nos obligations légales</li>
                </ul>
            </section>

            <section class="legal-section">
                <h2 class="section-title">
                    <i class="fas fa-key"></i>
                    4. Protection des Données
                </h2>
                <p>Herime Académie met en œuvre des mesures techniques et organisationnelles appropriées pour protéger vos données personnelles :</p>
                <ul>
                    <li><strong>Chiffrement SSL/TLS</strong> pour toutes les communications</li>
                    <li><strong>Stockage sécurisé</strong> de vos données sur des serveurs hébergés en toute sécurité</li>
                    <li><strong>Accès restreint</strong> aux données personnelles</li>
                    <li><strong>Mots de passe chiffrés</strong> pour la sécurité de votre compte</li>
                </ul>
                <p>Nous ne partageons jamais vos données avec des tiers à des fins commerciales sans votre consentement explicite.</p>
            </section>

            <section class="legal-section">
                <h2 class="section-title">
                    <i class="fas fa-share-alt"></i>
                    5. Partage de Données
                </h2>
                <p>Nous pouvons partager vos données avec :</p>
                <ul>
                    <li><strong>Prestataires de paiement</strong> (MaxiCash, PayPal) pour le traitement des transactions</li>
                    <li><strong>Hébergeurs et services cloud</strong> pour le fonctionnement de la plateforme</li>
                    <li><strong>Autorités légales</strong> si la loi l'exige</li>
                </ul>
                <p>Tous nos partenaires sont soumis à des obligations strictes de confidentialité.</p>
            </section>

            <section class="legal-section">
                <h2 class="section-title">
                    <i class="fas fa-cookie"></i>
                    6. Cookies
                </h2>
                <p>Nous utilisons des cookies pour :</p>
                <ul>
                    <li>Maintenir votre session de connexion</li>
                    <li>Mémoriser vos préférences</li>
                    <li>Améliorer votre expérience de navigation</li>
                    <li>Analyser le trafic et l'utilisation du site</li>
                </ul>
                <p>Vous pouvez gérer vos préférences de cookies dans les paramètres de votre navigateur.</p>
            </section>

            <section class="legal-section">
                <h2 class="section-title">
                    <i class="fas fa-user-cog"></i>
                    7. Vos Droits
                </h2>
                <p>Conformément à la réglementation, vous disposez des droits suivants :</p>
                <ul>
                    <li><strong>Droit d'accès</strong> : Accéder à vos données personnelles</li>
                    <li><strong>Droit de rectification</strong> : Corriger vos données inexactes</li>
                    <li><strong>Droit à l'effacement</strong> : Demander la suppression de vos données</li>
                    <li><strong>Droit à la portabilité</strong> : Récupérer vos données dans un format lisible</li>
                    <li><strong>Droit d'opposition</strong> : Vous opposer au traitement de vos données</li>
                    <li><strong>Droit de retrait du consentement</strong> : Retirer votre consentement à tout moment</li>
                </ul>
                <p>Pour exercer ces droits, contactez-nous à : <strong>contact@herime.com</strong></p>
            </section>

            <section class="legal-section">
                <h2 class="section-title">
                    <i class="fas fa-clock"></i>
                    8. Conservation des Données
                </h2>
                <p>Nous conservons vos données personnelles :</p>
                <ul>
                    <li><strong>Données de compte</strong> : Tant que votre compte est actif</li>
                    <li><strong>Données de transaction</strong> : 10 ans (obligation légale)</li>
                    <li><strong>Données de navigation</strong> : 13 mois maximum</li>
                    <li><strong>Données supprimées</strong> : Conservation 30 jours en sauvegarde</li>
                </ul>
            </section>

            <section class="legal-section">
                <h2 class="section-title">
                    <i class="fas fa-baby"></i>
                    9. Mineurs
                </h2>
                <p>Nos services sont destinés aux personnes majeures (18 ans et plus).</p>
                <p>Si vous avez moins de 18 ans, vous devez obtenir l'autorisation de vos parents ou tuteurs légaux avant de créer un compte.</p>
                <p>Nous ne collectons pas sciemment de données personnelles de mineurs sans autorisation parentale.</p>
            </section>

            <section class="legal-section">
                <h2 class="section-title">
                    <i class="fas fa-sync"></i>
                    10. Modifications de la Politique
                </h2>
                <p>Nous nous réservons le droit de modifier cette politique de confidentialité à tout moment.</p>
                <p>Toute modification sera publiée sur cette page avec la date de mise à jour.</p>
                <p>Nous vous recommandons de consulter régulièrement cette page.</p>
            </section>

            <section class="legal-section">
                <h2 class="section-title">
                    <i class="fas fa-phone"></i>
                    Contact
                </h2>
                <p>Pour toute question concernant cette politique de confidentialité ou vos données personnelles :</p>
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

.legal-section h3 {
    color: #003366;
    font-size: 1.2rem;
    font-weight: 600;
    margin-top: 20px;
    margin-bottom: 12px;
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

