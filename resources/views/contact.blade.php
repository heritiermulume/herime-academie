@extends('layouts.app')

@section('title', 'Contactez-nous - Herime Académie')
@section('description', 'Contactez l\'équipe Herime Académie pour toute question. Email, téléphone, WhatsApp ou formulaire de contact.')

@section('content')
<div class="legal-page">
    <div class="legal-header">
        <div class="legal-wrapper">
            <h1 class="legal-title">Contactez-nous</h1>
            <p class="legal-subtitle">Nous sommes là pour vous aider</p>
        </div>
    </div>

    <div class="legal-wrapper">
        <div class="legal-content">
            <section class="legal-section">
                <h2 class="section-title">
                    <i class="fas fa-info-circle"></i>
                    Comment nous contacter ?
                </h2>
                <p>L'équipe de <strong>Herime Académie</strong> est à votre disposition pour répondre à toutes vos questions concernant nos formations, le processus d'inscription, le paiement ou toute autre demande.</p>
                <p><strong>Herime Académie</strong> appartient à l'entreprise <strong>Herime</strong> (<a href="https://www.herime.com" target="_blank">www.herime.com</a>).</p>
            </section>

            <section class="legal-section">
                <h2 class="section-title">
                    <i class="fas fa-envelope"></i>
                    Par Email
                </h2>
                <div class="contact-method">
                    <div class="contact-icon">
                        <i class="fas fa-envelope"></i>
                    </div>
                    <div class="contact-details">
                        <h3>Email</h3>
                        <p>contact@herime.com</p>
                        <a href="mailto:contact@herime.com" class="btn btn-primary">
                            <i class="fas fa-paper-plane me-2"></i>Envoyer un email
                        </a>
                    </div>
                </div>
                <p class="mt-3"><strong>Réponse sous 24h ouvrées</strong></p>
            </section>

            <section class="legal-section">
                <h2 class="section-title">
                    <i class="fas fa-phone"></i>
                    Par Téléphone
                </h2>
                <div class="contact-method">
                    <div class="contact-icon">
                        <i class="fas fa-phone"></i>
                    </div>
                    <div class="contact-details">
                        <h3>Téléphone</h3>
                        <p>+243 824 449 218</p>
                        <a href="tel:+243824449218" class="btn btn-primary">
                            <i class="fas fa-phone me-2"></i>Appeler maintenant
                        </a>
                    </div>
                </div>
                <p class="mt-3"><strong>Disponible du lundi au vendredi, de 9h à 18h</strong></p>
            </section>

            <section class="legal-section">
                <h2 class="section-title">
                    <i class="fab fa-whatsapp"></i>
                    Via WhatsApp
                </h2>
                <div class="contact-method">
                    <div class="contact-icon whatsapp">
                        <i class="fab fa-whatsapp"></i>
                    </div>
                    <div class="contact-details">
                        <h3>WhatsApp</h3>
                        <p>+243 824 449 218</p>
                        <a href="https://wa.me/243824449218" class="btn btn-success" target="_blank">
                            <i class="fab fa-whatsapp me-2"></i>Ouvrir WhatsApp
                        </a>
                    </div>
                </div>
                <p class="mt-3"><strong>Service client WhatsApp disponible 24/7</strong></p>
            </section>

            <section class="legal-section">
                <h2 class="section-title">
                    <i class="fas fa-map-marker-alt"></i>
                    Notre Localisation
                </h2>
                <div class="contact-method">
                    <div class="contact-icon">
                        <i class="fas fa-map-marker-alt"></i>
                    </div>
                    <div class="contact-details">
                        <h3>Adresse</h3>
                        <p>Kinshasa, République Démocratique du Congo</p>
                        <button class="btn btn-outline-primary" disabled>
                            <i class="fas fa-map me-2"></i>Carte disponible bientôt
                        </button>
                    </div>
                </div>
            </section>

            <section class="legal-section">
                <h2 class="section-title">
                    <i class="fas fa-comment-dots"></i>
                    Formulaire de Contact
                </h2>
                <p>Remplissez le formulaire ci-dessous et nous vous répondrons dans les plus brefs délais :</p>
                
                <form class="contact-form">
                    @csrf
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label for="name" class="form-label">
                                <i class="fas fa-user me-2"></i>Nom complet <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control" id="name" name="name" required placeholder="Votre nom complet">
                        </div>
                        <div class="col-md-6">
                            <label for="email" class="form-label">
                                <i class="fas fa-envelope me-2"></i>Email <span class="text-danger">*</span>
                            </label>
                            <input type="email" class="form-control" id="email" name="email" required placeholder="votre@email.com">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="phone" class="form-label">
                            <i class="fas fa-phone me-2"></i>Téléphone
                        </label>
                        <input type="tel" class="form-control" id="phone" name="phone" placeholder="+243 XXX XXX XXX">
                    </div>
                    <div class="mb-3">
                        <label for="subject" class="form-label">
                            <i class="fas fa-tag me-2"></i>Sujet <span class="text-danger">*</span>
                        </label>
                        <select class="form-select" id="subject" name="subject" required>
                            <option value="">Sélectionnez un sujet</option>
                            <option value="inscription">Inscription à une formation</option>
                            <option value="paiement">Paiement</option>
                            <option value="technique">Problème technique</option>
                            <option value="support">Support pédagogique</option>
                            <option value="partenariat">Partenariat</option>
                            <option value="autre">Autre</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="message" class="form-label">
                            <i class="fas fa-comment me-2"></i>Message <span class="text-danger">*</span>
                        </label>
                        <textarea class="form-control" id="message" name="message" rows="6" required placeholder="Décrivez votre demande en détail..."></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary btn-lg w-100">
                        <i class="fas fa-paper-plane me-2"></i>Envoyer le message
                    </button>
                </form>
            </section>

            <section class="legal-section">
                <h2 class="section-title">
                    <i class="fas fa-clock"></i>
                    Horaires de Contact
                </h2>
                <div class="schedule-info">
                    <p><strong>Email :</strong> Réponse sous 24h ouvrées</p>
                    <p><strong>Téléphone :</strong> Lundi - Vendredi, 9h - 18h</p>
                    <p><strong>WhatsApp :</strong> Disponible 24h/24, 7j/7</p>
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

.contact-method {
    display: flex;
    align-items: flex-start;
    gap: 20px;
    padding: 20px;
    background: #f8f9fa;
    border-radius: 12px;
    border-left: 4px solid #003366;
    margin-bottom: 15px;
}

.contact-icon {
    background: #003366;
    color: white;
    width: 60px;
    height: 60px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 12px;
    font-size: 1.5rem;
    flex-shrink: 0;
}

.contact-icon.whatsapp {
    background: #25D366;
}

.contact-details {
    flex: 1;
}

.contact-details h3 {
    color: #003366;
    font-size: 1.2rem;
    font-weight: 600;
    margin-bottom: 8px;
}

.contact-details p {
    color: #6c757d;
    margin-bottom: 12px;
    font-size: 1.1rem;
}

.contact-form .form-label {
    color: #003366;
    font-weight: 500;
    margin-bottom: 8px;
}

.contact-form .form-label i {
    color: #ffcc33;
}

.contact-form .form-control,
.contact-form .form-select {
    border: 2px solid #e9ecef;
    border-radius: 8px;
    padding: 10px 15px;
    transition: all 0.3s ease;
}

.contact-form .form-control:focus,
.contact-form .form-select:focus {
    border-color: #003366;
    box-shadow: 0 0 0 0.2rem rgba(0,51,102,0.25);
}

.schedule-info {
    background: #f8f9fa;
    border-left: 4px solid #003366;
    padding: 20px;
    border-radius: 8px;
}

.schedule-info p {
    margin-bottom: 10px;
    color: #003366;
}

.legal-content a {
    color: #003366;
    text-decoration: underline;
    font-weight: 500;
}

.legal-content a:hover {
    color: #ffcc33;
}

.text-danger {
    color: #dc3545 !important;
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
    
    .contact-method {
        flex-direction: column;
        text-align: center;
    }
    
    .contact-icon {
        margin: 0 auto;
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
    
    .contact-icon {
        width: 50px;
        height: 50px;
        font-size: 1.3rem;
    }
    
    .contact-details h3 {
        font-size: 1.1rem;
    }
    
    .btn {
        width: 100%;
    }
}
</style>
@endpush
@endsection
