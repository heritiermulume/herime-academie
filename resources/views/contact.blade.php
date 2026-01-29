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
                <p class="text-center mb-5">L'équipe de <strong>Herime Académie</strong> est à votre disposition pour répondre à toutes vos questions concernant nos contenus, le processus d'inscription, le paiement ou toute autre demande.</p>
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
                        <p class="card-text text-muted small mb-3">academie@herime.com</p>
                        <a href="mailto:academie@herime.com" class="btn btn-primary btn-sm">
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
                        
                        <form class="contact-form" id="contactForm" action="{{ route('contact.store') }}" method="POST">
                            @csrf
                            <div id="contactFormAlert" class="alert d-none mb-3"></div>
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
                                <button type="submit" class="btn btn-primary btn-lg" id="submitBtn">
                                    <i class="fas fa-paper-plane me-2"></i><span class="btn-text">Envoyer le message</span>
                                    <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
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

@push('scripts')
<script>
(function() {
    'use strict';
    
    function initContactForm() {
        const form = document.getElementById('contactForm');
        if (!form) {
            console.error('Formulaire de contact non trouvé');
            return;
        }

        const alertDiv = document.getElementById('contactFormAlert');
        const submitBtn = document.getElementById('submitBtn');
        const btnText = submitBtn ? submitBtn.querySelector('.btn-text') : null;
        const spinner = submitBtn ? submitBtn.querySelector('.spinner-border') : null;

        function handleSubmit(e) {
            e.preventDefault();
            e.stopPropagation();

            // Réinitialiser l'alerte
            if (alertDiv) {
                alertDiv.classList.add('d-none');
                alertDiv.classList.remove('alert-success', 'alert-danger');
            }

            // Désactiver le bouton et afficher le spinner
            if (submitBtn) {
                submitBtn.disabled = true;
            }
            if (btnText) {
                btnText.textContent = 'Envoi en cours...';
            }
            if (spinner) {
                spinner.classList.remove('d-none');
            }

            // Récupérer les données du formulaire
            const formData = new FormData(form);

            // Envoyer la requête AJAX
            fetch(form.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                }
            })
            .then(response => {
                // Vérifier si la réponse est bien du JSON
                const contentType = response.headers.get('content-type');
                if (!contentType || !contentType.includes('application/json')) {
                    throw new Error('Réponse non-JSON reçue');
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    // Afficher le message de succès
                    if (alertDiv) {
                        alertDiv.classList.remove('d-none');
                        alertDiv.classList.add('alert-success');
                        alertDiv.innerHTML = '<i class="fas fa-check-circle me-2"></i>' + data.message;
                    }

                    // Réinitialiser le formulaire
                    form.reset();

                    // Scroll vers l'alerte
                    if (alertDiv) {
                        alertDiv.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    }
                } else {
                    // Afficher les erreurs
                    if (alertDiv) {
                        alertDiv.classList.remove('d-none');
                        alertDiv.classList.add('alert-danger');
                        
                        let errorMessage = data.message || 'Une erreur est survenue. Veuillez réessayer.';
                        
                        if (data.errors) {
                            const errorList = Object.values(data.errors).flat().join('<br>');
                            errorMessage += '<br>' + errorList;
                        }
                        
                        alertDiv.innerHTML = '<i class="fas fa-exclamation-circle me-2"></i>' + errorMessage;
                        alertDiv.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    }
                }
            })
            .catch(error => {
                console.error('Erreur:', error);
                if (alertDiv) {
                    alertDiv.classList.remove('d-none');
                    alertDiv.classList.add('alert-danger');
                    alertDiv.innerHTML = '<i class="fas fa-exclamation-circle me-2"></i>Une erreur est survenue lors de l\'envoi. Veuillez réessayer plus tard.';
                    alertDiv.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            })
            .finally(() => {
                // Réactiver le bouton et masquer le spinner
                if (submitBtn) {
                    submitBtn.disabled = false;
                }
                if (btnText) {
                    btnText.textContent = 'Envoyer le message';
                }
                if (spinner) {
                    spinner.classList.add('d-none');
                }
            });
        }

        form.addEventListener('submit', handleSubmit);
    }

    // Initialiser quand le DOM est prêt
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initContactForm);
    } else {
        initContactForm();
    }
})();
</script>
@endpush
@endsection
