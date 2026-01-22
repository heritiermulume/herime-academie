<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class CustomAnnouncementMail extends Mailable
{
    use Queueable, SerializesModels;

    public $subject;
    public $content;
    public $attachments;
    private $inlineImages = [];
    private $disableImageEmbedding = false;

    /**
     * Create a new message instance.
     */
    public function __construct(string $subject, string $content, array $attachments = [])
    {
        $this->subject = $subject;
        $this->content = $content;
        $this->attachments = $attachments;
        
        // Option pour désactiver l'embedding d'images si configuré
        // Utile si l'embedding cause des problèmes d'envoi
        $this->disableImageEmbedding = config('mail.disable_image_embedding', false);
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            from: new \Illuminate\Mail\Mailables\Address('academie@herime.com', 'Herime Académie'),
            subject: $this->subject,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        // Traiter le contenu pour améliorer l'affichage et convertir les images
        // Protéger contre les erreurs pour ne pas faire échouer l'envoi de l'email
        try {
            $processedContent = $this->processContent($this->content);
        } catch (\Exception $e) {
            \Log::error("Erreur lors du traitement du contenu de l'email: " . $e->getMessage(), [
                'error_class' => get_class($e),
                'trace' => $e->getTraceAsString()
            ]);
            // En cas d'erreur, utiliser le contenu original sans traitement
            $processedContent = $this->content;
        }
        
        return new Content(
            view: 'emails.custom-announcement',
            with: [
                'content' => $this->content,
                'processedContent' => $processedContent,
                'logoUrl' => config('app.url') . '/images/logo-herime-academie.png',
            ],
        );
    }
    
    /**
     * Traite le contenu pour améliorer l'affichage :
     * - Convertit les images en pièces jointes inline (CID) si possible
     * - Convertit les URLs en liens cliquables
     * - Réduit les espaces interlignes excessifs
     * - Améliore le formatage des boutons d'action
     */
    private function processContent(string $content): string
    {
        // Si l'embedding d'images est désactivé, ne pas convertir les images
        if ($this->disableImageEmbedding) {
            \Log::debug("Conversion d'images en CID désactivée, conservation des URLs originales");
        } else {
            try {
                // Étape 0 : Convertir les images en pièces jointes inline
                $content = $this->convertImagesToInline($content);
            } catch (\Exception $e) {
                \Log::error("Erreur lors de la conversion des images inline, désactivation de l'embedding: " . $e->getMessage(), [
                    'error_class' => get_class($e),
                    'trace' => $e->getTraceAsString()
                ]);
                // En cas d'erreur, désactiver l'embedding et continuer avec les URLs originales
                $this->disableImageEmbedding = true;
                $this->inlineImages = [];
            } catch (\Throwable $e) {
                \Log::error("Erreur fatale lors de la conversion des images inline, désactivation de l'embedding: " . $e->getMessage(), [
                    'error_class' => get_class($e),
                    'trace' => $e->getTraceAsString()
                ]);
                $this->disableImageEmbedding = true;
                $this->inlineImages = [];
            }
        }
        
        // Nettoyer d'abord le contenu HTML
        // Réduire les espaces multiples
        $content = preg_replace('/\s+/', ' ', $content);
        
        // Convertir les URLs en liens cliquables si elles ne le sont pas déjà
        // Stratégie : remplacer temporairement les liens existants, convertir les URLs, puis restaurer les liens
        
        // Étape 1 : Remplacer temporairement tous les liens existants par des placeholders
        $linkPlaceholders = [];
        $placeholderIndex = 0;
        $content = preg_replace_callback(
            '/<a[^>]*href=["\']([^"\']+)["\'][^>]*>([^<]*)<\/a>/i',
            function($matches) use (&$linkPlaceholders, &$placeholderIndex) {
                $placeholder = "___LINK_PLACEHOLDER_{$placeholderIndex}___";
                $linkPlaceholders[$placeholder] = [
                    'href' => htmlspecialchars($matches[1], ENT_QUOTES, 'UTF-8'),
                    'text' => $matches[2]
                ];
                $placeholderIndex++;
                return $placeholder;
            },
            $content
        );
        
        // Étape 2 : Convertir les URLs restantes en liens (celles qui ne sont pas dans des balises <a>)
        // Pattern simplifié sans lookbehind complexe
        $content = preg_replace_callback(
            '/(https?:\/\/[^\s<>"\'{}|\\^`\[\]]+)/i',
            function($matches) {
                $url = htmlspecialchars($matches[1], ENT_QUOTES, 'UTF-8');
                // Vérifier que ce n'est pas déjà un placeholder de lien
                if (strpos($matches[1], '___LINK_PLACEHOLDER_') === false) {
                    return '<a href="' . $url . '" style="color: #003366; text-decoration: underline; word-break: break-all;">' . $url . '</a>';
                }
                return $matches[0];
            },
            $content
        );
        
        // Étape 3 : Restaurer les liens originaux avec le bon style
        foreach ($linkPlaceholders as $placeholder => $linkData) {
            $styledLink = '<a href="' . $linkData['href'] . '" style="color: #003366; text-decoration: underline; word-break: break-all;">' . $linkData['text'] . '</a>';
            $content = str_replace($placeholder, $styledLink, $content);
        }
        
        // Réduire les espaces interlignes multiples dans les paragraphes
        // Remplacer plusieurs <br> consécutifs par un seul
        $content = preg_replace('/(<br\s*\/?>\s*){3,}/i', '<br><br>', $content);
        
        // Réduire les espaces entre les paragraphes (Quill génère souvent des <p><br></p>)
        $content = preg_replace('/<p[^>]*>\s*<br\s*\/?>\s*<\/p>/i', '<p></p>', $content);
        $content = preg_replace('/<p[^>]*>\s*<\/p>/i', '', $content);
        
        // Réduire les espaces entre les paragraphes
        $content = preg_replace('/<\/p>\s*<p[^>]*>/i', '</p><p>', $content);
        
        // Nettoyer les espaces en début et fin de div/p
        $content = preg_replace('/<(div|p)[^>]*>\s+/i', '<$1>', $content);
        $content = preg_replace('/\s+<\/(div|p)>/i', '</$1>', $content);
        
        // Réduire les espaces dans les divs vides
        $content = preg_replace('/<div[^>]*>\s*<\/div>/i', '', $content);
        
        // S'assurer que les boutons d'action ont le bon style
        $content = preg_replace_callback(
            '/<a[^>]*class=["\'][^"\']*action-button[^"\']*["\'][^>]*>/i',
            function($matches) {
                $button = $matches[0];
                // Déterminer la couleur selon la classe
                $backgroundColor = '#003366'; // primary par défaut
                if (strpos($button, 'secondary') !== false) {
                    $backgroundColor = '#6c757d';
                } elseif (strpos($button, 'success') !== false) {
                    $backgroundColor = '#28a745';
                } elseif (strpos($button, 'danger') !== false) {
                    $backgroundColor = '#dc3545';
                }
                
                // S'assurer que le style inline est présent et correct
                if (preg_match('/style=["\'][^"\']*["\']/', $button)) {
                    // Mettre à jour le style existant
                    $button = preg_replace(
                        '/style=["\']([^"\']*)["\']/',
                        'style="display: inline-block; padding: 12px 24px; margin: 15px 10px 15px 0; background-color: ' . $backgroundColor . '; color: #ffffff !important; text-decoration: none !important; border-radius: 6px; font-weight: 600; text-align: center;"',
                        $button
                    );
                } else {
                    // Ajouter le style
                    $button = str_replace(
                        '>',
                        ' style="display: inline-block; padding: 12px 24px; margin: 15px 10px 15px 0; background-color: ' . $backgroundColor . '; color: #ffffff !important; text-decoration: none !important; border-radius: 6px; font-weight: 600; text-align: center;">',
                        $button
                    );
                }
                return $button;
            },
            $content
        );
        
        // Nettoyer les espaces en fin de ligne
        $content = trim($content);
        
        return $content;
    }

    /**
     * Convertit les images du contenu en pièces jointes inline (CID)
     * 
     * @param string $content
     * @return string
     */
    private function convertImagesToInline(string $content): string
    {
        $this->inlineImages = [];
        $imageIndex = 0;
        
        // Extraire toutes les images du contenu
        $content = preg_replace_callback(
            '/<img[^>]+src=["\']([^"\']+)["\'][^>]*>/i',
            function($matches) use (&$imageIndex) {
                try {
                    $imageUrl = $matches[1];
                    
                    // Ignorer les images déjà en CID ou les images externes non gérées
                    if (strpos($imageUrl, 'cid:') === 0 || strpos($imageUrl, 'data:') === 0) {
                        return $matches[0];
                    }
                    
                    // Extraire le chemin du fichier depuis l'URL
                    $filePath = $this->extractFilePathFromUrl($imageUrl);
                    
                    if (!$filePath) {
                        // Si on ne peut pas extraire le chemin, garder l'image telle quelle
                        \Log::debug("Impossible d'extraire le chemin pour l'image: {$imageUrl}");
                        return $matches[0];
                    }
                    
                    // Vérifier que le fichier existe et est accessible
                    $disk = Storage::disk('local');
                    if (!$disk->exists($filePath)) {
                        \Log::warning("Image introuvable pour email inline: {$filePath} (URL: {$imageUrl})");
                        // Retirer l'image du contenu plutôt que de laisser une image cassée
                        return '';
                    }
                    
                    // Vérifier que le fichier n'est pas trop volumineux (limite de 10MB pour les emails)
                    try {
                        $fileSize = $disk->size($filePath);
                        if ($fileSize > 10 * 1024 * 1024) { // 10MB
                            \Log::warning("Image trop volumineuse pour email inline: {$filePath} ({$fileSize} bytes)");
                            // Retirer l'image du contenu
                            return '';
                        }
                    } catch (\Exception $e) {
                        \Log::warning("Impossible de vérifier la taille de l'image: {$filePath} - " . $e->getMessage());
                        return '';
                    }
                    
                    // Générer un CID unique avec extension
                    $imageIndex++;
                    $extension = pathinfo(basename($filePath), PATHINFO_EXTENSION) ?: 'jpg';
                    $cid = 'image' . $imageIndex;
                    $cidFilename = $cid . '.' . $extension;
                    
                    // Stocker l'image pour l'attacher plus tard
                    $this->inlineImages[$cidFilename] = [
                        'path' => $filePath,
                        'name' => basename($filePath)
                    ];
                    
                    // Remplacer l'URL par le CID dans la balise img
                    // Laravel génère le CID à partir du nom du fichier
                    $imgTag = $matches[0];
                    $imgTag = preg_replace('/src=["\'][^"\']+["\']/', 'src="cid:' . $cidFilename . '"', $imgTag);
                    
                    \Log::debug("Image convertie en CID: {$imageUrl} -> cid:{$cidFilename}");
                    
                    return $imgTag;
                } catch (\Exception $e) {
                    // En cas d'erreur lors du traitement d'une image, logger et garder l'image originale
                    \Log::error("Erreur lors de la conversion d'une image en CID: " . $e->getMessage(), [
                        'image_url' => $matches[1] ?? 'unknown',
                        'error_class' => get_class($e)
                    ]);
                    // Retourner l'image originale pour ne pas perdre le contenu
                    return $matches[0];
                }
            },
            $content
        );
        
        return $content;
    }
    
    /**
     * Extrait le chemin du fichier depuis une URL sécurisée
     * 
     * @param string $url
     * @return string|null
     */
    private function extractFilePathFromUrl(string $url): ?string
    {
        try {
            // Nettoyer l'URL (enlever les paramètres de requête, fragments, etc.)
            $parsedUrl = parse_url($url);
            $path = $parsedUrl['path'] ?? $url;
            
            // Parser l'URL pour extraire le type et le chemin
            // Format attendu: /files/{type}/{path}
            if (preg_match('#/files/([^/]+)/(.+)$#', $path, $matches)) {
                $type = $matches[1];
                $relativePath = urldecode($matches[2]);
                
                // Déterminer le chemin complet selon le type
                $basePath = '';
                switch ($type) {
                    case 'email-images':
                    case 'email_images':
                        $basePath = 'email-images';
                        break;
                    case 'thumbnails':
                        $basePath = 'courses/thumbnails';
                        break;
                    case 'previews':
                        $basePath = 'courses/previews';
                        break;
                    case 'lessons':
                        $basePath = 'courses/lessons';
                        break;
                    case 'downloads':
                        $basePath = 'courses/downloads';
                        break;
                    case 'avatars':
                        $basePath = 'avatars';
                        break;
                    case 'banners':
                        $basePath = 'banners';
                        break;
                    case 'media':
                        $basePath = 'media';
                        break;
                    case 'temporary':
                        $basePath = 'tmp/uploads';
                        break;
                    default:
                        \Log::debug("Type de fichier non reconnu dans l'URL: {$type} (URL: {$url})");
                        return null;
                }
                
                // Sécuriser le chemin pour éviter les traversées de répertoire
                $relativePath = str_replace('..', '', $relativePath);
                $relativePath = ltrim($relativePath, '/');
                
                // Nettoyer les caractères dangereux
                $relativePath = preg_replace('#[^a-zA-Z0-9._/-]#', '', $relativePath);
                
                if (empty($relativePath)) {
                    \Log::warning("Chemin relatif vide après nettoyage (URL: {$url})");
                    return null;
                }
                
                $fullPath = $basePath . '/' . $relativePath;
                \Log::debug("Chemin extrait de l'URL: {$url} -> {$fullPath}");
                
                return $fullPath;
            }
            
            // Si l'URL est déjà un chemin relatif (cas où l'image est déjà dans storage)
            $cleanPath = ltrim($path, '/');
            // Vérifier que c'est un chemin valide
            if (preg_match('#^(email-images|email_images|courses|avatars|banners|media|tmp/uploads)/#', $cleanPath)) {
                // Normaliser email_images en email-images
                $cleanPath = str_replace('email_images/', 'email-images/', $cleanPath);
                \Log::debug("Chemin direct détecté: {$url} -> {$cleanPath}");
                return $cleanPath;
            }
            
            // Si l'URL contient le domaine de l'application, essayer d'extraire le chemin
            $appUrl = config('app.url');
            if ($appUrl && strpos($url, $appUrl) === 0) {
                $relativePath = substr($url, strlen($appUrl));
                return $this->extractFilePathFromUrl($relativePath);
            }
            
            \Log::warning("Impossible d'extraire le chemin du fichier depuis l'URL: {$url}");
            return null;
        } catch (\Exception $e) {
            \Log::error("Erreur lors de l'extraction du chemin depuis l'URL: {$url} - " . $e->getMessage(), [
                'error_class' => get_class($e)
            ]);
            return null;
        }
    }

    /**
     * Build the message (appelé après envelope, content, et attachments)
     * Utilisé pour embed les images inline avec le bon CID
     */
    public function build()
    {
        // Si l'embedding est désactivé ou s'il n'y a pas d'images, ne rien faire
        if ($this->disableImageEmbedding || empty($this->inlineImages)) {
            return $this;
        }
        
        // Protéger complètement cette méthode pour qu'elle ne puisse jamais faire échouer l'envoi
        try {
            
            // Les images inline sont déjà dans $this->inlineImages avec leurs CID
            // On les embed maintenant avec embedData() pour garantir que le CID correspond exactement
            return $this->withSymfonyMessage(function ($message) {
                $embeddedCount = 0;
                $failedCount = 0;
                
                foreach ($this->inlineImages as $cidFilename => $imageData) {
                    try {
                        $disk = Storage::disk('local');
                        
                        if (!$disk->exists($imageData['path'])) {
                            \Log::warning("Image introuvable lors de l'embed: {$imageData['path']} (CID: {$cidFilename})");
                            $failedCount++;
                            continue;
                        }
                        
                        // Lire les données binaires de l'image
                        $imageDataContent = $disk->get($imageData['path']);
                        
                        if (empty($imageDataContent)) {
                            \Log::warning("Image vide lors de l'embed: {$imageData['path']} (CID: {$cidFilename})");
                            $failedCount++;
                            continue;
                        }
                        
                        // Vérifier le type MIME
                        $mimeType = $disk->mimeType($imageData['path']);
                        if (!$mimeType || !str_starts_with($mimeType, 'image/')) {
                            $mimeType = 'image/jpeg'; // Fallback
                            \Log::debug("Type MIME non détecté ou invalide pour {$imageData['path']}, utilisation de image/jpeg");
                        }
                        
                        // Embed l'image avec embedData() - le CID sera exactement le nom du fichier
                        // Cela garantit que cid:image1.jpg dans le HTML correspond à embedData(..., 'image1.jpg')
                        $message->embedData(
                            $imageDataContent,
                            $cidFilename,
                            ['mime' => $mimeType]
                        );
                        
                        $embeddedCount++;
                        \Log::debug("Image embedée avec succès: {$cidFilename} ({$imageData['path']})");
                        
                    } catch (\Symfony\Component\Mime\Exception\InvalidArgumentException $e) {
                        \Log::error("Erreur d'argument lors de l'embed de l'image inline {$cidFilename}: " . $e->getMessage(), [
                            'path' => $imageData['path'],
                            'trace' => $e->getTraceAsString()
                        ]);
                        $failedCount++;
                    } catch (\Exception $e) {
                        \Log::error("Erreur lors de l'embed de l'image inline {$cidFilename}: " . $e->getMessage(), [
                            'path' => $imageData['path'],
                            'error_class' => get_class($e),
                            'trace' => $e->getTraceAsString()
                        ]);
                        $failedCount++;
                    } catch (\Throwable $e) {
                        // Capturer même les erreurs fatales
                        \Log::error("Erreur fatale lors de l'embed de l'image inline {$cidFilename}: " . $e->getMessage(), [
                            'path' => $imageData['path'],
                            'error_class' => get_class($e),
                            'trace' => $e->getTraceAsString()
                        ]);
                        $failedCount++;
                    }
                }
                
                if ($embeddedCount > 0 || $failedCount > 0) {
                    \Log::info("Images embedées dans l'email: {$embeddedCount} réussie(s), {$failedCount} échouée(s)");
                }
                
                // Ne pas lancer d'exception même si certaines images ont échoué
                // L'email doit être envoyé même sans les images
            });
        } catch (\Exception $e) {
            // Si build() échoue complètement, logger l'erreur mais ne pas faire échouer l'envoi
            \Log::error("Erreur dans build() lors de l'envoi d'email avec images: " . $e->getMessage(), [
                'error_class' => get_class($e),
                'trace' => $e->getTraceAsString(),
                'inline_images_count' => count($this->inlineImages)
            ]);
            
            // Désactiver l'embedding pour éviter les problèmes futurs
            $this->disableImageEmbedding = true;
            $this->inlineImages = [];
            
            // Retourner $this pour permettre à l'email de continuer sans les images embedées
            // Note: Le contenu HTML contient déjà les références CID, mais elles ne seront pas embedées
            // Les clients email ne pourront pas afficher ces images, mais l'email sera envoyé
            return $this;
        } catch (\Throwable $e) {
            // Capturer même les erreurs fatales
            \Log::error("Erreur fatale dans build() lors de l'envoi d'email avec images: " . $e->getMessage(), [
                'error_class' => get_class($e),
                'trace' => $e->getTraceAsString(),
                'inline_images_count' => count($this->inlineImages)
            ]);
            
            // Désactiver l'embedding pour éviter les problèmes futurs
            $this->disableImageEmbedding = true;
            $this->inlineImages = [];
            
            return $this;
        }
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        $attachments = [];

        // Ajouter les pièces jointes normales (pas les images inline, elles sont gérées dans build())
        foreach ($this->attachments as $attachmentData) {
            try {
                // Gérer le cas où $attachmentData est un tableau (retour de FileUploadService)
                $attachmentPath = is_array($attachmentData) ? ($attachmentData['path'] ?? null) : $attachmentData;
                
                if (!$attachmentPath) {
                    continue;
                }
                
                // Le chemin est déjà relatif à storage/app/
                $fullPath = storage_path('app/' . ltrim($attachmentPath, '/'));
                
                if (file_exists($fullPath)) {
                    $attachments[] = Attachment::fromPath($fullPath);
                } else {
                    // Logger l'erreur mais continuer avec les autres fichiers
                    \Log::warning("Pièce jointe introuvable: {$fullPath}");
                }
            } catch (\Exception $e) {
                // Logger l'erreur mais continuer avec les autres fichiers
                \Log::error("Erreur lors de l'ajout de la pièce jointe: " . $e->getMessage());
            }
        }

        return $attachments;
    }
}




