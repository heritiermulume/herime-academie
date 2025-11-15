<?php

namespace App\Http\Controllers;

use App\Services\FileUploadService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Pion\Laravel\ChunkUpload\Exceptions\UploadMissingFileException;
use Pion\Laravel\ChunkUpload\Handler\HandlerFactory;
use Pion\Laravel\ChunkUpload\Receiver\FileReceiver;

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
        \Log::debug('Chunk upload request received', [
            'upload_type' => $request->input('upload_type'),
            'chunk_number' => $request->input('resumableChunkNumber'),
            'chunk_size' => $request->input('resumableChunkSize'),
            'total_chunks' => $request->input('resumableTotalChunks'),
            'file_size' => $request->input('resumableTotalSize'),
            'file_name' => $request->input('resumableFilename'),
            'content_length' => $request->headers->get('content-length'),
        ]);

        $receiver = new FileReceiver('file', $request, HandlerFactory::classFromRequest($request));

        if ($receiver->isUploaded() === false) {
            throw new UploadMissingFileException();
        }

        $save = $receiver->receive();

        if ($save->isFinished()) {
            $file = $save->getFile();

            $uploadType = $request->input('upload_type', 'lesson');
            $folder = $this->resolveFolder($uploadType);
            try {
                $result = $this->fileUploadService->uploadTemporary($file, $folder);
            } finally {
                $temporaryPath = $file->getPathname();
                if ($temporaryPath && file_exists($temporaryPath)) {
                    @unlink($temporaryPath);
                }
            }

            $storedPath = $result['path'];
            $disk = Storage::disk('local');

            $originalName = $request->input('original_name', $file->getClientOriginalName());
            $mimeType = $disk->mimeType($storedPath) ?: $file->getClientMimeType();
            $size = $disk->size($storedPath) ?: (int) $file->getSize();

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
}


