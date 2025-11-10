<?php

namespace App\Http\Controllers;

use App\Services\FileUploadService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
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
        $receiver = new FileReceiver('file', $request, HandlerFactory::classFromRequest($request));

        if ($receiver->isUploaded() === false) {
            throw new UploadMissingFileException();
        }

        $save = $receiver->receive();

        if ($save->isFinished()) {
            $file = $save->getFile();

            $uploadType = $request->input('upload_type', 'lesson');
            $folder = $this->resolveFolder($uploadType);
            $replacePath = $request->input('replace_path');

            try {
                $result = $this->fileUploadService->upload($file, $folder, $replacePath);
            } finally {
                $file->delete();
            }

            return response()->json([
                'status' => 'completed',
                'path' => $result['path'],
                'url' => $result['url'],
                'filename' => $request->input('original_name', $file->getClientOriginalName()),
                'mime_type' => $file->getMimeType(),
                'size' => (int) $file->getSize(),
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


