<?php

namespace App\Http\Controllers;

use App\Services\FileUploadService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Pion\Laravel\ChunkUpload\Exceptions\UploadMissingFileException;
use Pion\Laravel\ChunkUpload\Handler\HandlerFactory;
use Pion\Laravel\ChunkUpload\Receiver\FileReceiver;
use Exception;
use RuntimeException;

class ChunkUploadController extends Controller
{
    public function __construct(private readonly FileUploadService $fileUploadService)
    {
    }

    /**
     * Handle chunked uploads for large lesson files and preview videos.
     */
    public function handle(Request $request): JsonResponse
    {
        try {
            // Vérifier si post_max_size a été dépassé (PHP vide $_POST et $_FILES)
            if (empty($request->all()) && empty($request->files->all()) && $request->header('Content-Length')) {
                $contentLength = (int) $request->header('Content-Length');
                $postMaxSize = $this->parseSize(ini_get('post_max_size'));
                
                Log::error('Chunk upload failed: post_max_size exceeded', [
                    'content_length' => $contentLength,
                    'post_max_size' => $postMaxSize,
                    'post_max_size_ini' => ini_get('post_max_size'),
                    'upload_max_filesize' => ini_get('upload_max_filesize'),
                ]);

                return response()->json([
                    'message' => 'Le fichier est trop volumineux. La taille dépasse la limite du serveur (post_max_size: ' . ini_get('post_max_size') . '). Contactez l\'administrateur pour augmenter cette limite.',
                    'error' => 'POST_MAX_SIZE_EXCEEDED',
                    'content_length' => $contentLength,
                    'post_max_size' => $postMaxSize,
                ], 413);
            }

            Log::debug('Chunk upload request received', [
                'upload_type' => $request->input('upload_type'),
                'chunk_number' => $request->input('resumableChunkNumber'),
                'chunk_size' => $request->input('resumableChunkSize'),
                'total_chunks' => $request->input('resumableTotalChunks'),
                'file_size' => $request->input('resumableTotalSize'),
                'file_name' => $request->input('resumableFilename'),
                'content_length' => $request->headers->get('content-length'),
                'user_id' => auth()->id(),
                'has_files' => !empty($request->files->all()),
                'php_upload_max_filesize' => ini_get('upload_max_filesize'),
                'php_post_max_size' => ini_get('post_max_size'),
            ]);

            // Vérifier les permissions de base avant de commencer
            $this->checkStoragePermissions();

            $receiver = new FileReceiver('file', $request, HandlerFactory::classFromRequest($request));

            if ($receiver->isUploaded() === false) {
                Log::warning('Chunk upload failed: No file uploaded', [
                    'request_data' => $request->except(['file']),
                ]);
                return response()->json([
                    'message' => 'Aucun fichier n\'a été reçu. Veuillez réessayer.',
                    'error' => 'UPLOAD_MISSING_FILE',
                ], 400);
            }

            $save = $receiver->receive();

            if ($save->isFinished()) {
                $file = $save->getFile();

                if (!$file || !$file->isValid()) {
                    Log::error('Chunk upload failed: Invalid file after assembly', [
                        'file_error' => $file ? $file->getError() : 'file_is_null',
                    ]);
                    return response()->json([
                        'message' => 'Le fichier assemblé est invalide. Veuillez réessayer.',
                        'error' => 'INVALID_FILE',
                    ], 422);
                }

                $uploadType = $request->input('upload_type', 'lesson');
                $folder = $this->resolveFolder($uploadType);
                
                try {
                    $result = $this->fileUploadService->uploadTemporary($file, $folder);
                } catch (Exception $e) {
                    Log::error('Chunk upload failed: File upload service error', [
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString(),
                        'upload_type' => $uploadType,
                        'folder' => $folder,
                    ]);
                    
                    // Nettoyer le fichier temporaire en cas d'erreur
                    $temporaryPath = $file->getPathname();
                    if ($temporaryPath && file_exists($temporaryPath)) {
                        @unlink($temporaryPath);
                    }

                    $errorMessage = $this->getUserFriendlyErrorMessage($e);
                    
                    return response()->json([
                        'message' => $errorMessage,
                        'error' => 'UPLOAD_FAILED',
                    ], 500);
                } finally {
                    // Nettoyer le fichier temporaire
                    $temporaryPath = $file->getPathname();
                    if ($temporaryPath && file_exists($temporaryPath)) {
                        @unlink($temporaryPath);
                    }
                }

                $storedPath = $result['path'] ?? null;
                if (!$storedPath) {
                    Log::error('Chunk upload failed: No path returned from upload service');
                    return response()->json([
                        'message' => 'Le fichier n\'a pas pu être enregistré. Veuillez réessayer.',
                        'error' => 'STORAGE_FAILED',
                    ], 500);
                }

                $disk = Storage::disk('local');

                // Vérifier que le fichier existe bien
                if (!$disk->exists($storedPath)) {
                    Log::error('Chunk upload failed: File not found after upload', [
                        'stored_path' => $storedPath,
                    ]);
                    return response()->json([
                        'message' => 'Le fichier n\'a pas pu être trouvé après l\'upload. Veuillez réessayer.',
                        'error' => 'FILE_NOT_FOUND',
                    ], 500);
                }

                $originalName = $request->input('original_name', $file->getClientOriginalName());
                $mimeType = $disk->mimeType($storedPath) ?: $file->getClientMimeType();
                $size = $disk->size($storedPath) ?: (int) $file->getSize();

                Log::info('Chunk upload completed successfully', [
                    'stored_path' => $storedPath,
                    'file_size' => $size,
                    'upload_type' => $uploadType,
                ]);

                return response()->json([
                    'status' => 'completed',
                    'path' => $result['path'],
                    'url' => $result['url'],
                    'filename' => $originalName,
                    'mime_type' => $mimeType,
                    'size' => $size,
                ], 201);
            }

            $handler = $save->handler();
            $progress = $handler ? $handler->getPercentageDone() : 0;

            return response()->json([
                'status' => 'uploading',
                'progress' => $progress,
            ]);

        } catch (UploadMissingFileException $e) {
            Log::warning('Chunk upload failed: UploadMissingFileException', [
                'error' => $e->getMessage(),
            ]);
            return response()->json([
                'message' => 'Aucun fichier n\'a été reçu. Veuillez réessayer.',
                'error' => 'UPLOAD_MISSING_FILE',
            ], 400);

        } catch (Exception $e) {
            Log::error('Chunk upload failed: Unexpected error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            $errorMessage = $this->getUserFriendlyErrorMessage($e);

            return response()->json([
                'message' => $errorMessage,
                'error' => 'UNEXPECTED_ERROR',
            ], 500);
        }
    }

    private function resolveFolder(string $uploadType): string
    {
        return match ($uploadType) {
            'thumbnail' => 'courses/thumbnails',
            'preview' => 'courses/previews',
            'media', 'lesson' => 'courses/lessons',
            default => 'courses/lessons',
        };
    }

    /**
     * Vérifier les permissions de stockage
     */
    private function checkStoragePermissions(): void
    {
        $storagePath = storage_path('app');
        
        if (!is_dir($storagePath)) {
            throw new RuntimeException("Le dossier de stockage n'existe pas: {$storagePath}");
        }

        if (!is_writable($storagePath)) {
            throw new RuntimeException("Le dossier de stockage n'est pas accessible en écriture: {$storagePath}");
        }

        // Vérifier le dossier temporaire
        $tmpPath = storage_path('app/tmp');
        if (!is_dir($tmpPath)) {
            if (!@mkdir($tmpPath, 0755, true)) {
                throw new RuntimeException("Impossible de créer le dossier temporaire: {$tmpPath}");
            }
        }

        if (!is_writable($tmpPath)) {
            throw new RuntimeException("Le dossier temporaire n'est pas accessible en écriture: {$tmpPath}");
        }
    }

    /**
     * Obtenir un message d'erreur convivial pour l'utilisateur
     */
    private function getUserFriendlyErrorMessage(Exception $e): string
    {
        $message = $e->getMessage();

        // Messages spécifiques selon le type d'erreur
        if (str_contains($message, 'permission') || str_contains($message, 'Permission')) {
            return 'Erreur de permissions: Le serveur ne peut pas écrire les fichiers. Contactez l\'administrateur.';
        }

        if (str_contains($message, 'disk space') || str_contains($message, 'No space')) {
            return 'Espace disque insuffisant sur le serveur. Contactez l\'administrateur.';
        }

        if (str_contains($message, 'upload_max_filesize') || str_contains($message, 'post_max_size')) {
            return 'Le fichier est trop volumineux. La limite du serveur a été atteinte.';
        }

        if (str_contains($message, 'timeout') || str_contains($message, 'max_execution_time')) {
            return 'Le téléversement a pris trop de temps. Veuillez réessayer avec un fichier plus petit.';
        }

        if (str_contains($message, 'directory') || str_contains($message, 'dossier')) {
            return 'Erreur de configuration: Impossible de créer ou d\'accéder au dossier de stockage.';
        }

        // En production, ne pas exposer les détails techniques
        if (config('app.env') === 'production') {
            return 'Une erreur est survenue lors du téléversement. Veuillez réessayer. Si le problème persiste, contactez le support.';
        }

        // En développement, retourner le message complet
        return $message;
    }

    /**
     * Convertir une taille PHP (ex: "100M") en octets
     */
    private function parseSize(string $size): int
    {
        $size = trim($size);
        $last = strtolower($size[strlen($size) - 1] ?? '');
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
}


