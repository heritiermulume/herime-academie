<?php

namespace App\Http\Controllers;

use App\Services\FileUploadService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TemporaryUploadController extends Controller
{
    public function __construct(private readonly FileUploadService $fileUploadService)
    {
    }

    public function destroy(Request $request): JsonResponse
    {
        $paths = $request->input('paths');
        $singlePath = $request->input('path');

        if ($singlePath && !$paths) {
            $paths = [$singlePath];
        }

        if (!is_array($paths) || empty($paths)) {
            return response()->json([
                'message' => 'Aucun fichier temporaire à supprimer.',
            ], 422);
        }

        $deleted = [];
        $failed = [];

        foreach ($paths as $path) {
            if (!is_string($path) || trim($path) === '') {
                $failed[] = $path;
                continue;
            }

            $normalized = $this->fileUploadService->sanitizePath($path);

            if (!$this->fileUploadService->isTemporaryPath($normalized)) {
                $failed[] = $path;
                continue;
            }

            if ($this->fileUploadService->deleteTemporaryFile($normalized)) {
                $deleted[] = $normalized;
            } else {
                $failed[] = $path;
            }
        }

        return response()->json([
            'deleted' => $deleted,
            'failed' => $failed,
        ]);
    }
}






