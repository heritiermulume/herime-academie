@extends('layouts.app')

@section('title', 'À propos de nous - Herime Académie')
@section('description', 'Découvrez l\'histoire, la mission et la vision de Herime Académie, la plateforme d\'apprentissage en ligne du Groupe Herime.')

@section('content')
<div class="legal-page">
    <div class="legal-header">
        <div class="legal-wrapper">
            <h1 class="legal-title">À propos de nous</h1>
            <p class="legal-subtitle">Découvrez qui nous sommes</p>
        </div>
    </div>

    <div class="legal-wrapper">
        <div class="legal-content">
            <section class="legal-section">
                <h2 class="section-title">
                    <i class="fas fa-building"></i>
                    Qui sommes-nous ?
                </h2>
                <p><strong>Herime Académie</strong> appartient à l'entreprise <strong>Herime</strong> (<a href="https://www.herime.com" target="_blank">www.herime.com</a>), un groupe pionnier dans l'éducation numérique en République Démocratique du Congo.</p>
                <p>Depuis notre création, nous nous engageons à démocratiser l'accès à l'éducation de qualité en proposant des formations en ligne accessibles, innovantes et adaptées aux besoins locaux.</p>
            </section>

            <section class="legal-section">
                <h2 class="section-title">
                    <i class="fas fa-bullseye"></i>
                    Notre Mission
                </h2>
                <p>Notre mission est de rendre l'éducation de qualité accessible à tous, partout et à tout moment. Nous croyons fermement que l'apprentissage ne devrait pas être limité par des contraintes géographiques, financières ou temporelles.</p>
                <p>Chez Herime Académie, nous nous engageons à :</p>
                <ul>
                    <li>Offrir des cours de haute qualité dispensés par des experts</li>
                    <li>Favoriser l'autonomie et la flexibilité dans l'apprentissage</li>
                    <li>Construire une communauté d'apprenants passionnés</li>
                    <li>Permettre le développement personnel et professionnel de nos étudiants</li>
                </ul>
            </section>

            <section class="legal-section">
                <h2 class="section-title">
                    <i class="fas fa-eye"></i>
                    Notre Vision
                </h2>
                <p>Devenir la référence de l'éducation en ligne en Afrique centrale et francophone, en proposant des contenus pédagogiques innovants, pertinents et directement applicables dans le contexte local.</p>
                <p>Nous aspirons à :</p>
                <ul>
                    <li>Être la plateforme de choix pour les professionnels en quête de développement de compétences</li>
                    <li>Transformer la façon dont les Congolais et les Africains abordent l'éducation</li>
                    <li>Contribuer au développement économique local par la formation</li>
                    <li>Établir un réseau d'experts et d'apprenants à travers toute l'Afrique</li>
                </ul>
            </section>

            <section class="legal-section">
                <h2 class="section-title">
                    <i class="fas fa-heart"></i>
                    Nos Valeurs
                </h2>
                <div class="values-grid">
                    <div class="value-item">
                        <i class="fas fa-graduation-cap"></i>
                        <h3>Excellence</h3>
                        <p>Nous nous efforçons de maintenir les plus hauts standards de qualité dans tous nos cours et dans chaque interaction avec nos étudiants.</p>
                    </div>
                    <div class="value-item">
                        <i class="fas fa-unlock-alt"></i>
                        <h3>Accessibilité</h3>
                        <p>L'éducation doit être accessible à tous, indépendamment de leur localisation, de leur situation financière ou de leur emploi du temps.</p>
                    </div>
                    <div class="value-item">
                        <i class="fas fa-lightbulb"></i>
                        <h3>Innovation</h3>
                        <p>Nous adoptons les dernières technologies et méthodes pédagogiques pour offrir une expérience d'apprentissage exceptionnelle.</p>
                    </div>
                    <div class="value-item">
                        <i class="fas fa-users"></i>
                        <h3>Communauté</h3>
                        <p>Nous construisons une communauté solidaire et engagée d'apprenants qui s'entraident et progressent ensemble.</p>
                    </div>
                    <div class="value-item">
                        <i class="fas fa-handshake"></i>
                        <h3>Intégrité</h3>
                        <p>Nous agissons toujours avec honnêteté, transparence et respect dans nos relations avec nos étudiants et partenaires.</p>
                    </div>
                    <div class="value-item">
                        <i class="fas fa-rocket"></i>
                        <h3>Excellence Opérationnelle</h3>
                        <p>Nous optimisons continuellement nos processus pour offrir la meilleure expérience possible à nos utilisateurs.</p>
                    </div>
                </div>
            </section>

            <section class="legal-section">
                <h2 class="section-title">
                    <i class="fas fa-award"></i>
                    Pourquoi nous choisir ?
                </h2>
                <ul>
                    <li><strong>Contenus de qualité :</strong> Nos cours sont conçus et dispensés par des experts reconnus dans leurs domaines respectifs</li>
                    <li><strong>Flexibilité totale :</strong> Apprenez à votre rythme, quand et où vous voulez</li>
                    <li><strong>Accès illimité :</strong> Une fois acquis, votre formation reste accessible à vie</li>
                    <li><strong>Certificats reconnus :</strong> Obtenez des certifications valorisées sur le marché du travail</li>
                    <li><strong>Support dédié :</strong> Une équipe à votre écoute pour vous accompagner dans votre parcours</li>
                    <li><strong>Prix compétitifs :</strong> Des formations de qualité à des prix abordables</li>
                    <li><strong>Méthodes de paiement flexibles :</strong> Paiement en ligne sécurisé ou via Mobile Money</li>
                </ul>
            </section>

            <section class="legal-section">
                <h2 class="section-title">
                    <i class="fas fa-phone"></i>
                    Contactez-nous
                </h2>
                <p>Pour toute question ou pour en savoir plus sur Herime Académie :</p>
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
    background: linear-gradient(135deg, var(--primary-color) 0%, #004080 100%);
    color: white;
    padding: 80px 0 60px;
    margin-bottom: 50px;
    text-align: center;
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
    text-align: center;
}

.legal-subtitle {
    font-size: 1rem;
    opacity: 0.9;
    margin: 0;
    text-align: center;
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

.values-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 25px;
    margin-top: 25px;
}

.value-item {
    background: #f8f9fa;
    border: 2px solid #e9ecef;
    border-radius: 12px;
    padding: 25px;
    text-align: center;
    transition: all 0.3s ease;
}

.value-item:hover {
    border-color: #003366;
    box-shadow: 0 5px 15px rgba(0,51,102,0.1);
    transform: translateY(-5px);
}

.value-item i {
    font-size: 2.5rem;
    color: #003366;
    margin-bottom: 15px;
}

.value-item h3 {
    color: #003366;
    font-size: 1.2rem;
    font-weight: 600;
    margin-bottom: 12px;
}

.value-item p {
    color: #6c757d;
    margin: 0;
    line-height: 1.6;
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
@media (max-width: 991.98px) {
    .legal-header {
        padding: 60px 0 50px;
    }
    
    .legal-title {
        font-size: 1.75rem;
    }
    
    .legal-subtitle {
        font-size: 0.9rem;
    }
    
    /* Ajouter padding pour la navigation mobile en bas */
    .legal-content {
        padding-bottom: 60px;
    }
}

@media (max-width: 768px) {
    .legal-header {
        padding: 50px 0 40px;
    }
    
    .legal-title {
        font-size: 1.5rem;
    }
    
    .legal-subtitle {
        font-size: 0.875rem;
    }
    
    .legal-content {
        padding: 25px 20px;
        border-radius: 8px;
    }
    
    .section-title {
        font-size: 1.125rem;
        padding-bottom: 12px;
        margin-bottom: 16px;
    }
    
    .section-title i {
        font-size: 1rem;
    }
    
    .values-grid {
        grid-template-columns: 1fr;
        gap: 20px;
    }
}

@media (max-width: 480px) {
    .legal-header {
        padding: 40px 0 35px;
    }
    
    .legal-title {
        font-size: 1.375rem;
    }
    
    .legal-subtitle {
        font-size: 0.8125rem;
    }
    
    .legal-content {
        padding: 20px 15px;
    }
    
    .legal-section {
        margin-bottom: 30px;
    }
    
    .section-title {
        font-size: 1rem;
        padding-bottom: 10px;
        margin-bottom: 12px;
    }
    
    .section-title i {
        font-size: 0.9rem;
    }
    
    .value-item {
        padding: 18px;
    }
    
    .value-item i {
        font-size: 2rem;
        margin-bottom: 12px;
    }
    
    .value-item h3 {
        font-size: 1.1rem;
    }
    
    .legal-content ul {
        padding-left: 20px;
    }
    
    .legal-content li {
        margin-bottom: 8px;
        font-size: 0.9375rem;
    }
}
</style>
@endpush
@endsection
