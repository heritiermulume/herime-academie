<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class HandleUploadErrors
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Vérifier les erreurs d'upload PHP (compatible tests et différents serveurs)
        if ($request->isMethod('post') && !empty($request->files->all())) {
            foreach ($request->files->all() as $field => $file) {
                // $file peut être un UploadedFile ou un tableau si multiple
                $errorCode = is_array($file) && isset($file['error']) ? $file['error'] : (method_exists($file, 'getError') ? $file->getError() : UPLOAD_ERR_OK);
                if ($errorCode !== UPLOAD_ERR_OK) {
                    $errorMessage = $this->getUploadErrorMessage($file['error']);
                    
                    if ($request->expectsJson()) {
                        return response()->json([
                            'message' => $errorMessage,
                            'errors' => [$field => [$errorMessage]]
                        ], 422);
                    }
                    
                    return redirect()->back()
                        ->withErrors([$field => $errorMessage])
                        ->withInput();
                }
            }
        }

        return $next($request);
    }

    /**
     * Get user-friendly error message for upload errors
     */
    private function getUploadErrorMessage(int $errorCode): string
    {
        return match ($errorCode) {
            UPLOAD_ERR_INI_SIZE => 'Le fichier dépasse la limite de taille définie dans php.ini.',
            UPLOAD_ERR_FORM_SIZE => 'Le fichier dépasse la limite de taille définie dans le formulaire.',
            UPLOAD_ERR_PARTIAL => 'Le fichier n\'a été que partiellement uploadé.',
            UPLOAD_ERR_NO_FILE => 'Aucun fichier n\'a été uploadé.',
            UPLOAD_ERR_NO_TMP_DIR => 'Dossier temporaire manquant.',
            UPLOAD_ERR_CANT_WRITE => 'Impossible d\'écrire le fichier sur le disque.',
            UPLOAD_ERR_EXTENSION => 'Upload arrêté par une extension PHP.',
            default => 'Erreur inconnue lors de l\'upload du fichier.',
        };
    }
}