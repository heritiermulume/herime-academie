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

    /**
     * Create a new message instance.
     */
    public function __construct(string $subject, string $content, array $attachments = [])
    {
        $this->subject = $subject;
        $this->content = $content;
        $this->attachments = $attachments;
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
        $processedContent = $this->processContent($this->content);
        
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
     * - Convertit les images en pièces jointes inline (CID)
     * - Convertit les URLs en liens cliquables
     * - Réduit les espaces interlignes excessifs
     * - Améliore le formatage des boutons d'action
     */
    private function processContent(string $content): string
    {
        // Étape 0 : Convertir les images en pièces jointes inline
        $content = $this->convertImagesToInline($content);
        
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
                $imageUrl = $matches[1];
                
                // Ignorer les images déjà en CID ou les images externes non gérées
                if (strpos($imageUrl, 'cid:') === 0 || strpos($imageUrl, 'data:') === 0) {
                    return $matches[0];
                }
                
                // Extraire le chemin du fichier depuis l'URL
                $filePath = $this->extractFilePathFromUrl($imageUrl);
                
                if (!$filePath) {
                    // Si on ne peut pas extraire le chemin, garder l'image telle quelle
                    return $matches[0];
                }
                
                // Vérifier que le fichier existe
                $disk = Storage::disk('local');
                if (!$disk->exists($filePath)) {
                    \Log::warning("Image introuvable pour email inline: {$filePath}");
                    return $matches[0];
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
                
                return $imgTag;
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
        // Parser l'URL pour extraire le type et le chemin
        // Format attendu: /files/{type}/{path}
        if (preg_match('#/files/([^/]+)/(.+)$#', parse_url($url, PHP_URL_PATH) ?? '', $matches)) {
            $type = $matches[1];
            $relativePath = urldecode($matches[2]);
            
            // Déterminer le chemin complet selon le type
            $basePath = '';
            switch ($type) {
                case 'email-images':
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
                default:
                    return null;
            }
            
            // Sécuriser le chemin pour éviter les traversées de répertoire
            $relativePath = str_replace('..', '', $relativePath);
            $relativePath = ltrim($relativePath, '/');
            
            return $basePath . '/' . $relativePath;
        }
        
        // Si l'URL est déjà un chemin relatif (cas où l'image est déjà dans storage)
        if (strpos($url, 'email-images/') !== false || strpos($url, 'courses/') !== false) {
            $cleanPath = ltrim($url, '/');
            // Vérifier que c'est un chemin valide
            if (preg_match('#^(email-images|courses|avatars|banners|media)/#', $cleanPath)) {
                return $cleanPath;
            }
        }
        
        return null;
    }

    /**
     * Build the message (appelé après envelope, content, et attachments)
     * Utilisé pour embed les images inline avec le bon CID
     */
    public function build()
    {
        // Les images inline sont déjà dans $this->inlineImages avec leurs CID
        // On les embed maintenant avec embedData() pour garantir que le CID correspond exactement
        foreach ($this->inlineImages as $cidFilename => $imageData) {
            try {
                $disk = Storage::disk('local');
                
                if ($disk->exists($imageData['path'])) {
                    // Lire les données binaires de l'image
                    $imageDataContent = $disk->get($imageData['path']);
                    $mimeType = $disk->mimeType($imageData['path']) ?: 'image/jpeg';
                    
                    // Embed l'image avec embedData() - le CID sera exactement le nom du fichier
                    // Cela garantit que cid:image1.jpg dans le HTML correspond à embedData(..., 'image1.jpg')
                    $this->embedData(
                        $imageDataContent,
                        $cidFilename,
                        ['mime' => $mimeType]
                    );
                }
            } catch (\Exception $e) {
                \Log::error("Erreur lors de l'embed de l'image inline {$cidFilename}: " . $e->getMessage());
            }
        }
        
        return $this;
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




