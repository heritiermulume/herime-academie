<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use Illuminate\Http\Request;

class BannerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $banners = Banner::ordered()->paginate(20);
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
            'image' => 'required|image|mimes:jpeg,png,jpg,webp|max:5120',
            'mobile_image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
            'button1_text' => 'nullable|string|max:100',
            'button1_url' => 'nullable|string|max:500',
            'button1_style' => 'nullable|string|max:50',
            'button2_text' => 'nullable|string|max:100',
            'button2_url' => 'nullable|string|max:500',
            'button2_style' => 'nullable|string|max:50',
            'sort_order' => 'nullable|integer',
            'is_active' => 'boolean',
        ]);

        // Upload image principale - Stockage en base64
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $imageData = base64_encode(file_get_contents($file->getRealPath()));
            $mimeType = $file->getMimeType();
            $validated['image'] = 'data:' . $mimeType . ';base64,' . $imageData;
        }

        // Upload image mobile - Stockage en base64
        if ($request->hasFile('mobile_image')) {
            $file = $request->file('mobile_image');
            $imageData = base64_encode(file_get_contents($file->getRealPath()));
            $mimeType = $file->getMimeType();
            $validated['mobile_image'] = 'data:' . $mimeType . ';base64,' . $imageData;
        }

        $validated['is_active'] = $request->has('is_active');

        Banner::create($validated);

        return redirect()->route('admin.banners.index')
            ->with('success', 'Bannière créée avec succès.');
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
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'subtitle' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
            'mobile_image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
            'button1_text' => 'nullable|string|max:100',
            'button1_url' => 'nullable|string|max:500',
            'button1_style' => 'nullable|string|max:50',
            'button2_text' => 'nullable|string|max:100',
            'button2_url' => 'nullable|string|max:500',
            'button2_style' => 'nullable|string|max:50',
            'sort_order' => 'nullable|integer',
            'is_active' => 'boolean',
        ]);

        // Upload nouvelle image principale si fournie - Stockage en base64
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $imageData = base64_encode(file_get_contents($file->getRealPath()));
            $mimeType = $file->getMimeType();
            $validated['image'] = 'data:' . $mimeType . ';base64,' . $imageData;
        }

        // Upload nouvelle image mobile si fournie - Stockage en base64
        if ($request->hasFile('mobile_image')) {
            $file = $request->file('mobile_image');
            $imageData = base64_encode(file_get_contents($file->getRealPath()));
            $mimeType = $file->getMimeType();
            $validated['mobile_image'] = 'data:' . $mimeType . ';base64,' . $imageData;
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
        // Les images sont stockées en base de données, pas besoin de suppression physique
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
