<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Queue\SerializesModels;

class CustomAnnouncementMail extends Mailable
{
    use Queueable, SerializesModels;

    public $subject;
    public $content;
    public $attachments;

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
        // Traiter le contenu pour améliorer l'affichage
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
     * - Convertit les URLs en liens cliquables
     * - Réduit les espaces interlignes excessifs
     * - Améliore le formatage des boutons d'action
     */
    private function processContent(string $content): string
    {
        // Nettoyer d'abord le contenu HTML
        // Réduire les espaces multiples
        $content = preg_replace('/\s+/', ' ', $content);
        
        // Convertir les URLs en liens cliquables si elles ne le sont pas déjà
        // D'abord, marquer toutes les URLs déjà dans des liens
        $content = preg_replace_callback(
            '/<a[^>]*href=["\']([^"\']+)["\'][^>]*>([^<]*)<\/a>/i',
            function($matches) {
                // Garder les liens existants tels quels mais s'assurer qu'ils sont cliquables
                $href = htmlspecialchars($matches[1], ENT_QUOTES, 'UTF-8');
                $text = $matches[2];
                return '<a href="' . $href . '" style="color: #003366; text-decoration: underline; word-break: break-all;">' . $text . '</a>';
            },
            $content
        );
        
        // Ensuite, convertir les URLs qui ne sont pas dans des liens
        // Pattern pour détecter les URLs qui ne sont pas déjà dans des balises <a>
        $pattern = '/(?<!href=["\']|>)(?<!<a[^>]*>)(?<!["\'])(https?:\/\/[^\s<>"\'{}|\\^`\[\]]+)(?!["\'])/i';
        $content = preg_replace_callback($pattern, function($matches) {
            $url = htmlspecialchars($matches[1], ENT_QUOTES, 'UTF-8');
            return '<a href="' . $url . '" style="color: #003366; text-decoration: underline; word-break: break-all;">' . $url . '</a>';
        }, $content);
        
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
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        $attachments = [];

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




