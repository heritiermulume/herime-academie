@extends('layouts.app')

@section('title', 'Contactez-nous - Herime Académie')
@section('description', 'Contactez l\'équipe Herime Académie pour toute question. Email, téléphone, WhatsApp ou formulaire de contact.')

@section('content')
<!-- Page Header Section -->
<section class="page-header-section">
    <div class="container">
        <div class="row">
            <div class="col-12 text-center">
                <h1>Contactez-nous</h1>
                <p class="lead">Nous sommes là pour vous aider</p>
            </div>
        </div>
    </div>
</section>

<!-- Page Content Section -->
<section class="page-content-section">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <p class="text-center mb-5">L'équipe de <strong>Herime Académie</strong> est à votre disposition pour répondre à toutes vos questions concernant nos formations, le processus d'inscription, le paiement ou toute autre demande.</p>
            </div>
        </div>

        <!-- Contact Methods Grid -->
        <div class="row g-4 mb-5">
            <!-- Email -->
            <div class="col-md-6 col-lg-3">
                <div class="card h-100 text-center border-0 shadow-sm contact-card">
                    <div class="card-body p-4">
                        <div class="contact-icon-wrapper mb-3">
                            <i class="fas fa-envelope"></i>
                        </div>
                        <h5 class="card-title mb-2">Email</h5>
                        <p class="card-text text-muted small mb-3">contact@herime.com</p>
                        <a href="mailto:contact@herime.com" class="btn btn-primary btn-sm">
                            <i class="fas fa-paper-plane me-2"></i>Envoyer
                        </a>
                        <p class="small text-muted mt-3 mb-0">Réponse sous 24h ouvrées</p>
                    </div>
                </div>
            </div>

            <!-- Téléphone -->
            <div class="col-md-6 col-lg-3">
                <div class="card h-100 text-center border-0 shadow-sm contact-card">
                    <div class="card-body p-4">
                        <div class="contact-icon-wrapper mb-3">
                            <i class="fas fa-phone"></i>
                        </div>
                        <h5 class="card-title mb-2">Téléphone</h5>
                        <p class="card-text text-muted small mb-3">+243 824 449 218</p>
                        <a href="tel:+243824449218" class="btn btn-primary btn-sm">
                            <i class="fas fa-phone me-2"></i>Appeler
                        </a>
                        <p class="small text-muted mt-3 mb-0">Lun-Ven, 9h-18h</p>
                    </div>
                </div>
            </div>

            <!-- WhatsApp -->
            <div class="col-md-6 col-lg-3">
                <div class="card h-100 text-center border-0 shadow-sm contact-card whatsapp-card">
                    <div class="card-body p-4">
                        <div class="contact-icon-wrapper mb-3 whatsapp-icon">
                            <i class="fab fa-whatsapp"></i>
                        </div>
                        <h5 class="card-title mb-2">WhatsApp</h5>
                        <p class="card-text text-muted small mb-3">+243 824 449 218</p>
                        <a href="https://wa.me/243824449218" class="btn btn-success btn-sm" target="_blank">
                            <i class="fab fa-whatsapp me-2"></i>Ouvrir
                        </a>
                        <p class="small text-muted mt-3 mb-0">Disponible 24/7</p>
                    </div>
                </div>
            </div>

            <!-- Localisation -->
            <div class="col-md-6 col-lg-3">
                <div class="card h-100 text-center border-0 shadow-sm contact-card">
                    <div class="card-body p-4">
                        <div class="contact-icon-wrapper mb-3">
                            <i class="fas fa-map-marker-alt"></i>
                        </div>
                        <h5 class="card-title mb-2">Adresse</h5>
                        <p class="card-text text-muted small mb-3">Kinshasa, RDC</p>
                        <button class="btn btn-outline-primary btn-sm" disabled>
                            <i class="fas fa-map me-2"></i>Carte bientôt
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Contact Form -->
        <div class="row">
            <div class="col-12">
                <div class="card border-0 shadow-lg">
                    <div class="card-body p-4 p-md-5">
                        <h2 class="section-title-modern mb-4 text-center">
                            <i class="fas fa-comment-dots me-2"></i>
                            Formulaire de Contact
                        </h2>
                        <p class="text-center text-muted mb-4">Remplissez le formulaire ci-dessous et nous vous répondrons dans les plus brefs délais</p>
                        
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
                            <div class="mb-4">
                                <label for="message" class="form-label">
                                    <i class="fas fa-comment me-2"></i>Message <span class="text-danger">*</span>
                                </label>
                                <textarea class="form-control" id="message" name="message" rows="6" required placeholder="Décrivez votre demande en détail..."></textarea>
                            </div>
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="fas fa-paper-plane me-2"></i>Envoyer le message
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Horaires -->
        <div class="row mt-5">
            <div class="col-12 col-lg-8 mx-auto">
                <div class="alert alert-info border-0 shadow-sm">
                    <h5 class="alert-heading mb-3">
                        <i class="fas fa-clock me-2"></i>Horaires de Contact
                    </h5>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <strong>Email :</strong>
                            <p class="mb-0 small">Réponse sous 24h ouvrées</p>
                        </div>
                        <div class="col-md-4">
                            <strong>Téléphone :</strong>
                            <p class="mb-0 small">Lundi - Vendredi, 9h - 18h</p>
                        </div>
                        <div class="col-md-4">
                            <strong>WhatsApp :</strong>
                            <p class="mb-0 small">Disponible 24h/24, 7j/7</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

@push('styles')
<style>
/* Contact Cards */
.contact-card {
    transition: all 0.3s ease;
    border-radius: 16px;
}

.contact-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 25px rgba(0, 51, 102, 0.15) !important;
}

.contact-icon-wrapper {
    width: 80px;
    height: 80px;
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

.contact-card.whatsapp-card .contact-icon-wrapper.whatsapp-icon {
    background: linear-gradient(135deg, #25D366 0%, #128C7E 100%);
}

/* Responsive */
@media (max-width: 767.98px) {
    .contact-icon-wrapper {
        width: 70px;
        height: 70px;
        font-size: 1.75rem;
    }
}

@media (max-width: 480px) {
    .contact-icon-wrapper {
        width: 60px;
        height: 60px;
        font-size: 1.5rem;
    }
}
</style>
@endpush
@endsection
