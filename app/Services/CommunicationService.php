<?php

namespace App\Services;

use App\Models\User;
use App\Jobs\SendWhatsAppFromEmailJob;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Mail\Mailable;

class CommunicationService
{
    protected WhatsAppService $whatsappService;

    public function __construct(WhatsAppService $whatsappService)
    {
        $this->whatsappService = $whatsappService;
    }

    /**
     * Envoie un email et un message WhatsApp en parallèle
     * Si l'un échoue, l'autre continue
     * 
     * @param User $user L'utilisateur destinataire
     * @param Mailable $mailable L'email à envoyer
     * @param string|null $whatsappMessage Message WhatsApp personnalisé (optionnel)
     * @param bool $sendWhatsApp Si false, n'envoie que l'email
     * @return array ['email' => ['success' => bool, 'error' => string|null], 'whatsapp' => ['success' => bool, 'error' => string|null]]
     */
    public function sendEmailAndWhatsApp(
        User $user,
        Mailable $mailable,
        ?string $whatsappMessage = null,
        bool $sendWhatsApp = true
    ): array {
        $results = [
            'email' => ['success' => false, 'error' => null],
            'whatsapp' => ['success' => false, 'error' => null]
        ];

        // Envoyer l'email
        try {
            if ($user->email) {
                Mail::to($user->email)->send($mailable);
                $results['email'] = ['success' => true, 'error' => null];
                Log::info("Email envoyé avec succès à {$user->email}", [
                    'user_id' => $user->id,
                    'mailable' => get_class($mailable)
                ]);
            } else {
                $results['email'] = ['success' => false, 'error' => 'Aucun email pour cet utilisateur'];
                Log::warning("Tentative d'envoi d'email à un utilisateur sans email", ['user_id' => $user->id]);
            }
        } catch (\Exception $e) {
            $results['email'] = ['success' => false, 'error' => $e->getMessage()];
            Log::error("Erreur lors de l'envoi d'email", [
                'user_id' => $user->id,
                'user_email' => $user->email,
                'error' => $e->getMessage(),
                'mailable' => get_class($mailable)
            ]);
        }

        // Envoyer WhatsApp en parallèle (si activé et si l'utilisateur a un numéro)
        if ($sendWhatsApp) {
            if (!$user->phone) {
                $results['whatsapp'] = ['success' => false, 'error' => 'Aucun numéro de téléphone pour cet utilisateur'];
                Log::warning("Tentative d'envoi WhatsApp à un utilisateur sans numéro", [
                    'user_id' => $user->id,
                    'user_email' => $user->email,
                    'mailable' => get_class($mailable)
                ]);
            } else {
                try {
                    // Générer le message WhatsApp si non fourni
                    if (!$whatsappMessage) {
                        $whatsappMessage = $this->generateWhatsAppMessageFromMailable($mailable, $user);
                    } else {
                        // Si un message personnalisé est fourni, ajouter quand même l'en-tête et le pied
                        $whatsappMessage = $this->formatWhatsAppMessage($whatsappMessage, $user);
                    }

                    if ($whatsappMessage) {
                        // Envoyer via job pour ne pas bloquer
                        $queueConnection = config('queue.default');
                        $queueDriver = config("queue.connections.{$queueConnection}.driver", 'sync');
                        
                        Log::info("Démarrage envoi WhatsApp", [
                            'user_id' => $user->id,
                            'user_phone' => $user->phone,
                            'queue_driver' => $queueDriver,
                            'mailable' => get_class($mailable)
                        ]);
                        
                        if ($queueDriver === 'sync') {
                            // Exécution synchrone immédiate
                            SendWhatsAppFromEmailJob::dispatchSync($user, $whatsappMessage);
                            Log::info("Job WhatsApp exécuté en mode sync pour {$user->phone}", [
                                'user_id' => $user->id,
                                'mailable' => get_class($mailable)
                            ]);
                        } else {
                            // Exécution asynchrone via queue
                            SendWhatsAppFromEmailJob::dispatchAfterResponse($user, $whatsappMessage);
                            Log::info("Job WhatsApp dispatché en mode async pour {$user->phone}", [
                                'user_id' => $user->id,
                                'mailable' => get_class($mailable)
                            ]);
                        }
                        
                        $results['whatsapp'] = ['success' => true, 'error' => null];
                    } else {
                        $results['whatsapp'] = ['success' => false, 'error' => 'Impossible de générer le message WhatsApp'];
                        Log::warning("Impossible de générer le message WhatsApp", [
                            'user_id' => $user->id,
                            'user_phone' => $user->phone,
                            'mailable' => get_class($mailable)
                        ]);
                    }
                } catch (\Exception $e) {
                    $results['whatsapp'] = ['success' => false, 'error' => $e->getMessage()];
                    Log::error("Erreur lors du dispatch WhatsApp", [
                        'user_id' => $user->id,
                        'user_phone' => $user->phone,
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString(),
                        'mailable' => get_class($mailable)
                    ]);
                }
            }
        }

        return $results;
    }

    /**
     * Ajoute l'en-tête et le pied de page à un message WhatsApp
     * 
     * @param string $message Le message principal
     * @param User $user L'utilisateur destinataire
     * @return string
     */
    protected function formatWhatsAppMessage(string $message, User $user): string
    {
        $userName = $user->name ?? 'Cher utilisateur';
        
        $header = "━━━━━━━━━━━━━━━━━\n" .
                  "🎓 *HERIME ACADÉMIE*\n" .
                  "━━━━━━━━━━━━━━━━━\n\n";
        
        $footer = "\n\n" .
                  "━━━━━━━━━━━━━━━━━\n" .
                  "📚 _Herime Académie - Votre plateforme d'apprentissage en ligne._\n" .
                  "🌐 academie.herime.com\n" .
                  "📧 academie@herime.com\n" .
                  "━━━━━━━━━━━━━━━━━";
        
        return $header . $message . $footer;
    }

    /**
     * Génère un message WhatsApp à partir d'un Mailable
     * 
     * @param Mailable $mailable
     * @param User $user L'utilisateur destinataire
     * @return string|null
     */
    protected function generateWhatsAppMessageFromMailable(Mailable $mailable, User $user): ?string
    {
        $mailableClass = get_class($mailable);
        $userName = $user->name ?? 'Cher utilisateur';
        
        // Messages personnalisés selon le type d'email
        switch ($mailableClass) {
            case \App\Mail\CourseEnrolledMail::class:
                $course = $mailable->course;
                
                // Personnaliser selon le type de contenu
                if ($course->is_downloadable) {
                    // Contenu téléchargeable
                    if ($course->is_free) {
                        // Téléchargeable gratuit
                        $courseUrl = route('contents.show', $course->slug);
                        $message = "🎁 *Contenu gratuit disponible !*\n\n" .
                                  "Bonjour *{$userName}*,\n\n" .
                                  "Félicitations ! Vous avez maintenant accès à ce contenu gratuit :\n" .
                                  "*{$course->title}*\n\n" .
                                  "Vous pouvez le télécharger dès maintenant et en profiter à tout moment.\n\n" .
                                  "👉 {$courseUrl}\n\n" .
                                  "Bonne découverte !";
                    } else {
                        // Téléchargeable payant
                        $courseUrl = route('contents.show', $course->slug);
                        $message = "✅ *Achat confirmé !*\n\n" .
                                  "Bonjour *{$userName}*,\n\n" .
                                  "Votre achat a été confirmé avec succès. Vous avez maintenant accès à :\n" .
                                  "*{$course->title}*\n\n" .
                                  "Vous pouvez télécharger ce produit immédiatement.\n\n" .
                                  "👉 {$courseUrl}\n\n" .
                                  "Merci pour votre confiance !";
                    }
                } else {
                    // Contenu non téléchargeable
                    if ($course->is_free) {
                        // Non téléchargeable gratuit
                        $courseUrl = route('learning.course', $course->slug);
                        $message = "🎓 *Inscription confirmée !*\n\n" .
                                  "Bonjour *{$userName}*,\n\n" .
                                  "Félicitations ! Vous êtes maintenant inscrit au cours :\n" .
                                  "*{$course->title}*\n\n" .
                                  "Vous pouvez commencer votre apprentissage dès maintenant.\n\n" .
                                  "👉 {$courseUrl}\n\n" .
                                  "Bon apprentissage !";
                    } else {
                        // Non téléchargeable payant
                        $courseUrl = route('learning.course', $course->slug);
                        $message = "✅ *Achat confirmé !*\n\n" .
                                  "Bonjour *{$userName}*,\n\n" .
                                  "Votre achat a été confirmé avec succès. Vous avez maintenant accès au cours :\n" .
                                  "*{$course->title}*\n\n" .
                                  "Vous pouvez commencer votre apprentissage dès maintenant.\n\n" .
                                  "👉 {$courseUrl}\n\n" .
                                  "Merci pour votre confiance !";
                    }
                }
                return $this->formatWhatsAppMessage($message, $user);
            
            case \App\Mail\PaymentReceivedMail::class:
                $order = property_exists($mailable, 'order') ? $mailable->order : null;
                if (!$order) {
                    return null;
                }
                
                // Déterminer le type de contenus achetés
                $order->load(['orderItems.course']);
                $orderItems = $order->orderItems;
                $hasDownloadable = $orderItems->contains(function ($item) {
                    return $item->course && $item->course->is_downloadable;
                });
                $hasNonDownloadable = $orderItems->contains(function ($item) {
                    return $item->course && !$item->course->is_downloadable;
                });
                
                if ($hasDownloadable && !$hasNonDownloadable) {
                    // Uniquement des produits digitaux / téléchargeables
                    $contentType = "produits digitaux";
                    $actionText = "Téléchargez-les maintenant depuis votre espace personnel.";
                } elseif (!$hasDownloadable && $hasNonDownloadable) {
                    // Uniquement des cours classiques
                    $contentType = "cours";
                    $actionText = "Commencez votre apprentissage dès maintenant.";
                } elseif ($hasDownloadable && $hasNonDownloadable) {
                    // Panier mixte
                    $contentType = "cours et produits digitaux";
                    $actionText = "Accédez à vos contenus depuis votre espace personnel.";
                } else {
                    // Fallback générique
                    $contentType = "contenus";
                    $actionText = "Accédez à vos contenus depuis votre espace personnel.";
                }
                
                $message = "✅ *Paiement reçu*\n\n" .
                          "Bonjour *{$userName}*,\n\n" .
                          "Votre paiement pour la commande *{$order->order_number}* a été confirmé.\n\n" .
                          "Montant : *" . number_format($order->total, 0, ',', ' ') . " FCFA*\n\n" .
                          "Vous avez maintenant accès à tous vos {$contentType}.\n\n" .
                          "{$actionText}\n\n" .
                          "Merci pour votre confiance !";
                return $this->formatWhatsAppMessage($message, $user);
            
            case \App\Mail\InvoiceMail::class:
                $order = property_exists($mailable, 'order') ? $mailable->order : null;
                if (!$order) {
                    return null;
                }
                $message = "📄 *Facture disponible*\n\n" .
                          "Bonjour *{$userName}*,\n\n" .
                          "Votre facture pour la commande *{$order->order_number}* est disponible.\n\n" .
                          "Montant : *" . number_format($order->total, 0, ',', ' ') . " FCFA*\n\n" .
                          "Consultez votre espace personnel pour télécharger la facture.";
                return $this->formatWhatsAppMessage($message, $user);
            
            case \App\Mail\PaymentFailedMail::class:
                $order = property_exists($mailable, 'order') ? $mailable->order : null;
                if (!$order) {
                    return null;
                }
                $reason = property_exists($mailable, 'failureReason') && $mailable->failureReason 
                    ? $mailable->failureReason 
                    : 'Raison non spécifiée';
                $message = "❌ *Échec du paiement*\n\n" .
                          "Bonjour *{$userName}*,\n\n" .
                          "Le paiement pour la commande *{$order->order_number}* a échoué.\n\n" .
                          "Raison : {$reason}\n\n" .
                          "Veuillez réessayer ou contacter le support.";
                return $this->formatWhatsAppMessage($message, $user);
            
            case \App\Mail\CourseAccessRevokedMail::class:
                $course = property_exists($mailable, 'course') ? $mailable->course : null;
                if (!$course) {
                    return null;
                }
                $message = "⚠️ *Accès révoqué*\n\n" .
                          "Bonjour *{$userName}*,\n\n" .
                          "Votre accès au cours *{$course->title}* a été révoqué.\n\n" .
                          "Pour plus d'informations, contactez le support.";
                return $this->formatWhatsAppMessage($message, $user);
            
            case \App\Mail\CertificateIssuedMail::class:
                $certificate = property_exists($mailable, 'certificate') ? $mailable->certificate : null;
                if (!$certificate || !$certificate->course) {
                    return null;
                }
                $course = $certificate->course;
                $message = "🎉 *Certificat disponible*\n\n" .
                          "Bonjour *{$userName}*,\n\n" .
                          "Félicitations ! Votre certificat pour le cours *{$course->title}* est disponible.\n\n" .
                          "Téléchargez-le depuis votre espace personnel.";
                return $this->formatWhatsAppMessage($message, $user);
            
            case \App\Mail\OrderDeletedMail::class:
                $order = property_exists($mailable, 'order') ? $mailable->order : null;
                if (!$order) {
                    return null;
                }
                $message = "🗑️ *Commande annulée*\n\n" .
                          "Bonjour *{$userName}*,\n\n" .
                          "Votre commande *{$order->order_number}* a été annulée.\n\n" .
                          "Pour plus d'informations, contactez le support.";
                return $this->formatWhatsAppMessage($message, $user);
            
            case \App\Mail\ProviderPayoutReceivedMail::class:
                $payout = property_exists($mailable, 'payout') ? $mailable->payout : null;
                if (!$payout) {
                    return null;
                }
                $message = "💰 *Paiement reçu*\n\n" .
                          "Bonjour *{$userName}*,\n\n" .
                          "Votre paiement de *" . number_format($payout->amount, 0, ',', ' ') . " FCFA* a été effectué.\n\n" .
                          "Merci pour votre contribution !";
                return $this->formatWhatsAppMessage($message, $user);
            
            case \App\Mail\NewsletterWelcome::class:
                $subscriber = property_exists($mailable, 'subscriber') ? $mailable->subscriber : null;
                if (!$subscriber) {
                    return null;
                }
                $subscriberName = $subscriber->name ?? $userName;
                $message = "👋 *Bienvenue !*\n\n" .
                          "Bonjour *{$subscriberName}*,\n\n" .
                          "Merci de vous être inscrit à notre newsletter !\n\n" .
                          "Vous recevrez nos dernières actualités et offres spéciales.";
                return $this->formatWhatsAppMessage($message, $user);
            
            case \App\Mail\CustomAnnouncementMail::class:
                // Pour les annonces personnalisées, extraire le texte du HTML
                $subject = property_exists($mailable, 'subject') ? $mailable->subject : 'Annonce';
                $content = property_exists($mailable, 'content') ? $this->htmlToText($mailable->content) : '';
                $message = "*{$subject}*\n\n" .
                          "Bonjour *{$userName}*,\n\n" .
                          "{$content}";
                return $this->formatWhatsAppMessage($message, $user);
            
            default:
                // Message générique : extraire le sujet et convertir le HTML en texte
                try {
                    $envelope = $mailable->envelope();
                    $subject = $envelope->subject ?? 'Notification';
                    
                    // Essayer d'extraire le contenu
                    $content = '';
                    try {
                        $contentObj = $mailable->content();
                        if (isset($contentObj->view)) {
                            // Pour les vues, on ne peut pas facilement extraire le contenu
                            // On utilise juste le sujet
                            $content = '';
                        }
                    } catch (\Exception $e) {
                        // Ignorer
                    }
                    
                    $message = "*{$subject}*\n\n" .
                              "Bonjour *{$userName}*,\n\n" .
                              ($content ?: "Vous avez reçu une nouvelle notification.\n\nConsultez votre espace personnel pour plus de détails.");
                    return $this->formatWhatsAppMessage($message, $user);
                } catch (\Exception $e) {
                    Log::warning("Impossible de générer un message WhatsApp pour {$mailableClass}", [
                        'error' => $e->getMessage()
                    ]);
                    return null;
                }
        }
    }

    /**
     * Convertit du HTML en texte simple pour WhatsApp
     * 
     * @param string $html
     * @return string
     */
    protected function htmlToText(string $html): string
    {
        // Supprimer les balises HTML
        $text = strip_tags($html);
        
        // Décoder les entités HTML
        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        
        // Nettoyer les espaces multiples
        $text = preg_replace('/\s+/', ' ', $text);
        
        // Limiter la longueur (WhatsApp a une limite de 4096 caractères)
        if (mb_strlen($text) > 3500) {
            $text = mb_substr($text, 0, 3500) . '...';
        }
        
        return trim($text);
    }
}

