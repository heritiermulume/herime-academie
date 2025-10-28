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

        // Upload image principale
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('images/hero'), $filename);
            $validated['image'] = 'images/hero/' . $filename;
        }

        // Upload image mobile
        if ($request->hasFile('mobile_image')) {
            $file = $request->file('mobile_image');
            $filename = 'mobile_' . time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('images/hero'), $filename);
            $validated['mobile_image'] = 'images/hero/' . $filename;
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

        // Upload nouvelle image principale si fournie
        if ($request->hasFile('image')) {
            // Supprimer l'ancienne image
            if ($banner->image && file_exists(public_path($banner->image))) {
                unlink(public_path($banner->image));
            }
            $file = $request->file('image');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('images/hero'), $filename);
            $validated['image'] = 'images/hero/' . $filename;
        }

        // Upload nouvelle image mobile si fournie
        if ($request->hasFile('mobile_image')) {
            // Supprimer l'ancienne image mobile
            if ($banner->mobile_image && file_exists(public_path($banner->mobile_image))) {
                unlink(public_path($banner->mobile_image));
            }
            $file = $request->file('mobile_image');
            $filename = 'mobile_' . time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('images/hero'), $filename);
            $validated['mobile_image'] = 'images/hero/' . $filename;
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
        // Supprimer les images associées
        if ($banner->image && file_exists(public_path($banner->image))) {
            unlink(public_path($banner->image));
        }
        if ($banner->mobile_image && file_exists(public_path($banner->mobile_image))) {
            unlink(public_path($banner->mobile_image));
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
