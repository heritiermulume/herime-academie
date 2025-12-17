/**
 * Moneroo Failure Detector
 * 
 * Détecte les messages d'erreur affichés par Moneroo et signale l'échec au backend
 * pour envoyer immédiatement les notifications à l'utilisateur
 * 
 * Cas d'usage:
 * - Solde insuffisant
 * - Carte rejetée
 * - Transaction refusée
 * - Erreur de connexion
 * - Etc.
 */

(function() {
    'use strict';
    
    // Configuration
    const CONFIG = {
        checkInterval: 2000, // Vérifier toutes les 2 secondes
        maxChecks: 30, // Maximum 30 vérifications (1 minute)
        reportEndpoint: '/moneroo/report-failure',
        debug: false, // Désactiver les logs en production
    };
    
    let checkCount = 0;
    let failureReported = false;
    let paymentId = null;
    
    /**
     * Logger pour le débogage
     */
    function log(...args) {
        if (CONFIG.debug) {
            console.log('[Moneroo Failure Detector]', ...args);
        }
    }
    
    /**
     * Extraire le payment_id de l'URL
     */
    function extractPaymentId() {
        const urlParams = new URLSearchParams(window.location.search);
        return urlParams.get('payment_id') || urlParams.get('paymentId');
    }
    
    /**
     * Déterminer le type d'échec depuis le message
     */
    function determineFailureType(message) {
        const lowerMessage = message.toLowerCase();
        
        if (lowerMessage.includes('solde') || lowerMessage.includes('insuffisant') || 
            lowerMessage.includes('insufficient') || lowerMessage.includes('balance')) {
            return 'insufficient_funds';
        }
        
        if (lowerMessage.includes('carte') || lowerMessage.includes('card') || 
            lowerMessage.includes('invalide') || lowerMessage.includes('invalid') ||
            lowerMessage.includes('expir')) {
            return 'invalid_card';
        }
        
        if (lowerMessage.includes('refus') || lowerMessage.includes('declined') || 
            lowerMessage.includes('rejet')) {
            return 'transaction_declined';
        }
        
        if (lowerMessage.includes('connexion') || lowerMessage.includes('network') || 
            lowerMessage.includes('internet')) {
            return 'network_error';
        }
        
        if (lowerMessage.includes('timeout') || lowerMessage.includes('délai') || 
            lowerMessage.includes('temps')) {
            return 'timeout';
        }
        
        if (lowerMessage.includes('annul') || lowerMessage.includes('cancel')) {
            return 'user_cancelled';
        }
        
        return 'unknown';
    }
    
    /**
     * Signaler l'échec au backend
     */
    async function reportFailure(failureMessage, failureType) {
        if (failureReported) {
            log('Échec déjà signalé, ignorer');
            return;
        }
        
        if (!paymentId) {
            log('Pas de payment_id, impossible de signaler l\'échec');
            return;
        }
        
        failureReported = true;
        log('Signalement de l\'échec au backend:', { paymentId, failureMessage, failureType });
        
        try {
            const response = await fetch(CONFIG.reportEndpoint, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                    'Accept': 'application/json',
                },
                body: JSON.stringify({
                    payment_id: paymentId,
                    failure_message: failureMessage,
                    failure_type: failureType,
                }),
            });
            
            const data = await response.json();
            
            if (data.success) {
                log('Échec signalé avec succès, notifications envoyées');
            } else {
                log('Erreur lors du signalement:', data.message);
            }
        } catch (error) {
            log('Exception lors du signalement:', error);
        }
    }
    
    /**
     * Détecter les messages d'erreur sur la page
     */
    function detectFailureMessages() {
        // Sélecteurs pour les messages d'erreur courants
        const errorSelectors = [
            '.alert-danger',
            '.error-message',
            '.payment-error',
            '.alert.alert-danger',
            '[class*="error"]',
            '[class*="danger"]',
            '[class*="failed"]',
            '[role="alert"]',
        ];
        
        for (const selector of errorSelectors) {
            const elements = document.querySelectorAll(selector);
            
            for (const element of elements) {
                const text = element.textContent.trim();
                
                // Ignorer les messages vides ou trop courts
                if (!text || text.length < 10) {
                    continue;
                }
                
                // Vérifier si c'est un message d'erreur de paiement
                const isPaymentError = 
                    text.toLowerCase().includes('paiement') ||
                    text.toLowerCase().includes('payment') ||
                    text.toLowerCase().includes('transaction') ||
                    text.toLowerCase().includes('solde') ||
                    text.toLowerCase().includes('balance') ||
                    text.toLowerCase().includes('carte') ||
                    text.toLowerCase().includes('card') ||
                    text.toLowerCase().includes('échec') ||
                    text.toLowerCase().includes('failed') ||
                    text.toLowerCase().includes('erreur') ||
                    text.toLowerCase().includes('error');
                
                if (isPaymentError) {
                    log('Message d\'erreur détecté:', text);
                    const failureType = determineFailureType(text);
                    reportFailure(text, failureType);
                    return true;
                }
            }
        }
        
        return false;
    }
    
    /**
     * Vérifier périodiquement les messages d'erreur
     */
    function startMonitoring() {
        log('Démarrage de la surveillance des erreurs Moneroo');
        
        const interval = setInterval(() => {
            checkCount++;
            
            if (checkCount > CONFIG.maxChecks) {
                log('Nombre maximum de vérifications atteint, arrêt de la surveillance');
                clearInterval(interval);
                return;
            }
            
            if (failureReported) {
                log('Échec déjà signalé, arrêt de la surveillance');
                clearInterval(interval);
                return;
            }
            
            const errorDetected = detectFailureMessages();
            
            if (errorDetected) {
                clearInterval(interval);
            }
        }, CONFIG.checkInterval);
    }
    
    /**
     * Initialisation
     */
    function init() {
        // Vérifier si on est sur une page Moneroo ou une page de retour
        const isMonerooPage = 
            window.location.href.includes('moneroo') ||
            window.location.href.includes('checkout') ||
            document.querySelector('[data-moneroo]') !== null;
        
        if (!isMonerooPage) {
            log('Pas une page Moneroo, surveillance désactivée');
            return;
        }
        
        // Extraire le payment_id
        paymentId = extractPaymentId();
        
        if (!paymentId) {
            log('Pas de payment_id trouvé, surveillance limitée');
            // On continue quand même au cas où le payment_id serait ajouté plus tard
        } else {
            log('Payment ID détecté:', paymentId);
        }
        
        // Vérifier immédiatement
        detectFailureMessages();
        
        // Démarrer la surveillance périodique
        startMonitoring();
    }
    
    // Démarrer quand le DOM est prêt
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
    
    // Exposer globalement pour les tests
    window.MonerooFailureDetector = {
        reportFailure,
        detectFailureMessages,
        extractPaymentId,
    };
    
})();

