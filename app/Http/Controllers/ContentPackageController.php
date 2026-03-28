<?php

namespace App\Http\Controllers;

use App\Models\ContentPackage;

class ContentPackageController extends Controller
{
    public function index()
    {
        return redirect()->to(route('contents.index').'#content-packs');
    }

    public function show(ContentPackage $package)
    {
        $package->load([
            'contents' => fn ($q) => $q->with(['category', 'provider', 'sections.lessons', 'reviews'])
                ->orderByPivot('sort_order'),
        ]);

        return view('packs.show', compact('package'));
    }
}
