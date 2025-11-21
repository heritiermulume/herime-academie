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
        // Vérifier si post_max_size a été dépassé (PHP vide $_POST et $_FILES dans ce cas)
        if ($request->isMethod('post') && empty($request->all()) && empty($request->files->all()) && $request->header('Content-Length')) {
            $contentLength = (int) $request->header('Content-Length');
            $postMaxSize = $this->parseSize(ini_get('post_max_size'));
            
            \Illuminate\Support\Facades\Log::error('Post max size exceeded', [
                'content_length' => $contentLength,
                'post_max_size' => $postMaxSize,
                'route' => $request->route()?->getName(),
                'url' => $request->fullUrl(),
            ]);

            if ($request->expectsJson() || $request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'message' => 'Le fichier est trop volumineux. La taille dépasse la limite du serveur (post_max_size: ' . ini_get('post_max_size') . '). Contactez l\'administrateur.',
                    'error' => 'POST_MAX_SIZE_EXCEEDED',
                    'content_length' => $contentLength,
                    'post_max_size' => $postMaxSize,
                ], 413);
            }

            return redirect()->back()
                ->withErrors(['file' => 'Le fichier est trop volumineux. La taille dépasse la limite du serveur.'])
                ->withInput();
        }

        // Vérifier les erreurs d'upload PHP (compatible tests et différents serveurs)
        $routeName = $request->route()?->getName();
        $isChunkUpload = in_array($routeName, ['admin.uploads.chunk', 'instructor.uploads.chunk'], true);

        // Pour les routes de chunk upload, vérifier aussi les erreurs PHP
        if ($request->isMethod('post') && $request->hasFile('file')) {
            $file = $request->file('file');
            
            if ($file && $file->isValid() === false) {
                $errorCode = $file->getError();
                $errorMessage = $this->getUploadErrorMessage($errorCode);
                
                \Illuminate\Support\Facades\Log::error('PHP upload error detected', [
                    'error_code' => $errorCode,
                    'error_message' => $errorMessage,
                    'route' => $routeName,
                    'file_name' => $file->getClientOriginalName(),
                    'file_size' => $file->getSize(),
                ]);

                if ($request->expectsJson() || $request->ajax() || $request->wantsJson() || $isChunkUpload) {
                    return response()->json([
                        'message' => $errorMessage,
                        'error' => 'UPLOAD_ERROR',
                        'error_code' => $errorCode,
                    ], 422);
                }
                
                return redirect()->back()
                    ->withErrors(['file' => $errorMessage])
                    ->withInput();
            }
        }

        // Vérifier les erreurs pour les autres uploads (non-chunk)
        if ($request->isMethod('post') && !empty($request->files->all()) && !$isChunkUpload) {
            foreach ($request->files->all() as $field => $file) {
                // $file peut être un UploadedFile ou un tableau si multiple
                $errorCode = is_array($file) && isset($file['error']) 
                    ? $file['error'] 
                    : (method_exists($file, 'getError') ? $file->getError() : UPLOAD_ERR_OK);
                    
                if ($errorCode !== UPLOAD_ERR_OK) {
                    $errorMessage = $this->getUploadErrorMessage($errorCode);
                    
                    \Illuminate\Support\Facades\Log::error('Upload error', [
                        'field' => $field,
                        'error_code' => $errorCode,
                        'error_message' => $errorMessage,
                    ]);
                    
                    if ($request->expectsJson() || $request->ajax() || $request->wantsJson()) {
                        return response()->json([
                            'message' => $errorMessage,
                            'errors' => [$field => [$errorMessage]],
                            'error_code' => $errorCode,
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
     * Convertir une taille PHP (ex: "100M") en octets
     */
    private function parseSize(string $size): int
    {
        $size = trim($size);
        $last = strtolower($size[strlen($size) - 1]);
        $size = (int) $size;

        switch ($last) {
            case 'g':
                $size *= 1024;
                // no break
            case 'm':
                $size *= 1024;
                // no break
            case 'k':
                $size *= 1024;
        }

        return $size;
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