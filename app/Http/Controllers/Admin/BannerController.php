<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use App\Services\FileUploadService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BannerController extends Controller
{
    protected $fileUploadService;

    public function __construct(FileUploadService $fileUploadService)
    {
        $this->fileUploadService = $fileUploadService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $banners = Banner::ordered()->paginate(15);
        return view('admin.banners.index', compact('banners'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.banners.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'subtitle' => 'nullable|string',
            'image' => 'required|image|mimes:jpeg,png,jpg,webp|max:20480',
            'mobile_image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:20480',
            'button1_text' => 'nullable|string|max:100',
            'button1_url' => 'nullable|string|max:500',
            'button1_style' => 'nullable|string|max:50',
            'button1_target' => 'nullable|string|in:_self,_blank',
            'button2_text' => 'nullable|string|max:100',
            'button2_url' => 'nullable|string|max:500',
            'button2_style' => 'nullable|string|max:50',
            'button2_target' => 'nullable|string|in:_self,_blank',
            'sort_order' => 'nullable|integer',
        ], [
            'image.max' => 'L\'image ne doit pas dépasser 20 Mo.',
            'mobile_image.max' => 'L\'image mobile ne doit pas dépasser 20 Mo.',
        ]);

        try {
            // Upload image principale - Stockage dans le système de fichiers
            if ($request->hasFile('image')) {
                $result = $this->fileUploadService->uploadImage(
                    $request->file('image'),
                    'banners',
                    null,
                    1920 // Max 1920px width
                );
                $validated['image'] = $result['path'];
            }

            // Upload image mobile - Stockage dans le système de fichiers
            if ($request->hasFile('mobile_image')) {
                $result = $this->fileUploadService->uploadImage(
                    $request->file('mobile_image'),
                    'banners',
                    null,
                    1920 // Max 1920px width
                );
                $validated['mobile_image'] = $result['path'];
            }

            $validated['is_active'] = $request->has('is_active');

            Banner::create($validated);

            return redirect()->route('admin.banners.index')
                ->with('success', 'Bannière créée avec succès.');
        } catch (\Exception $e) {
            \Log::error('Erreur lors de la création de la bannière: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            return back()->withErrors(['error' => 'Erreur lors de la création de la bannière: ' . $e->getMessage()])->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Banner $banner)
    {
        return view('admin.banners.show', compact('banner'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Banner $banner)
    {
        return view('admin.banners.edit', compact('banner'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Banner $banner)
    {
        // Si c'est juste un changement d'ordre
        if ($request->has('sort_order') && count($request->all()) <= 4) {
            $oldOrder = $banner->sort_order;
            $newOrder = $request->input('sort_order');
            
            if ($oldOrder != $newOrder) {
                // Réorganiser les autres bannières
                if ($newOrder < $oldOrder) {
                    // Déplacer vers le haut
                    Banner::where('sort_order', '>=', $newOrder)
                          ->where('sort_order', '<', $oldOrder)
                          ->increment('sort_order');
                } else {
                    // Déplacer vers le bas
                    Banner::where('sort_order', '>', $oldOrder)
                          ->where('sort_order', '<=', $newOrder)
                          ->decrement('sort_order');
                }
                
                $banner->update(['sort_order' => $newOrder]);
            }
            
            return redirect()->route('admin.banners.index')
                ->with('success', 'Ordre modifié avec succès.');
        }
        
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'subtitle' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:20480',
            'mobile_image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:20480',
            'button1_text' => 'nullable|string|max:100',
            'button1_url' => 'nullable|string|max:500',
            'button1_style' => 'nullable|string|max:50',
            'button1_target' => 'nullable|string|in:_self,_blank',
            'button2_text' => 'nullable|string|max:100',
            'button2_url' => 'nullable|string|max:500',
            'button2_style' => 'nullable|string|max:50',
            'button2_target' => 'nullable|string|in:_self,_blank',
            'sort_order' => 'nullable|integer',
        ], [
            'image.max' => 'L\'image ne doit pas dépasser 20 Mo.',
            'mobile_image.max' => 'L\'image mobile ne doit pas dépasser 20 Mo.',
        ]);

        // Upload nouvelle image principale si fournie - Stockage dans le système de fichiers
        if ($request->hasFile('image')) {
            // Déterminer l'ancien chemin (si ce n'est pas base64)
            $oldPath = null;
            if ($banner->image && !str_starts_with($banner->image, 'data:')) {
                $oldPath = preg_replace('#^storage/#', '', $banner->image);
            }
            
            $result = $this->fileUploadService->uploadImage(
                $request->file('image'),
                'banners',
                $oldPath,
                1920 // Max 1920px width
            );
            // Stocker le chemin relatif
            $validated['image'] = $result['path'];
        }

        // Upload nouvelle image mobile si fournie - Stockage dans le système de fichiers
        if ($request->hasFile('mobile_image')) {
            // Déterminer l'ancien chemin (si ce n'est pas base64)
            $oldPath = null;
            if ($banner->mobile_image && !str_starts_with($banner->mobile_image, 'data:')) {
                $oldPath = preg_replace('#^storage/#', '', $banner->mobile_image);
            }
            
            $result = $this->fileUploadService->uploadImage(
                $request->file('mobile_image'),
                'banners',
                $oldPath,
                1920 // Max 1920px width
            );
            // Stocker le chemin relatif
            $validated['mobile_image'] = $result['path'];
        }

        $validated['is_active'] = $request->has('is_active');

        $banner->update($validated);

        return redirect()->route('admin.banners.index')
            ->with('success', 'Bannière mise à jour avec succès.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Banner $banner)
    {
        // Supprimer les fichiers images si ce ne sont pas des images base64
        if ($banner->image && !str_starts_with($banner->image, 'data:')) {
            $imagePath = ltrim(preg_replace('#^storage/#', '', $banner->image), '/');
            if (Storage::disk('local')->exists($imagePath)) {
                Storage::disk('local')->delete($imagePath);
            }
        }
        
        if ($banner->mobile_image && !str_starts_with($banner->mobile_image, 'data:')) {
            $mobileImagePath = ltrim(preg_replace('#^storage/#', '', $banner->mobile_image), '/');
            if (Storage::disk('local')->exists($mobileImagePath)) {
                Storage::disk('local')->delete($mobileImagePath);
            }
        }
        
        $banner->delete();

        return redirect()->route('admin.banners.index')
            ->with('success', 'Bannière supprimée avec succès.');
    }

    /**
     * Toggle active status
     */
    public function toggleActive(Banner $banner)
    {
        $banner->update(['is_active' => !$banner->is_active]);

        return response()->json([
            'success' => true,
            'is_active' => $banner->is_active
        ]);
    }
}
