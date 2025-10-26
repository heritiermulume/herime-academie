<?php

namespace App\Http\Controllers;

use App\Models\BlogPost;
use App\Models\BlogCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class BlogController extends Controller
{
    public function index(Request $request)
    {
        $query = BlogPost::published()->with(['author', 'category']);
        
        // Filtrage par catégorie
        if ($request->has('category') && $request->category) {
            $query->where('category_id', $request->category);
        }
        
        // Recherche
        if ($request->has('search') && $request->search) {
            $query->where(function($q) use ($request) {
                $q->where('title', 'like', '%' . $request->search . '%')
                  ->orWhere('content', 'like', '%' . $request->search . '%');
            });
        }
        
        $posts = $query->latest()->paginate(12);
        $categories = BlogCategory::active()->get();
        $featuredPosts = BlogPost::published()->featured()->limit(3)->get();
        
        return view('blog.index', compact('posts', 'categories', 'featuredPosts'));
    }

    public function show(BlogPost $post)
    {
        if (!$post->is_published) {
            abort(404);
        }
        
        $post->load(['author', 'category']);
        
        // Incrémenter les vues
        $post->increment('views');
        
        // Articles similaires
        $relatedPosts = BlogPost::published()
            ->where('id', '!=', $post->id)
            ->where('category_id', $post->category_id)
            ->limit(3)
            ->get();
        
        // Articles récents
        $recentPosts = BlogPost::published()
            ->where('id', '!=', $post->id)
            ->latest()
            ->limit(5)
            ->get();
        
        return view('blog.show', compact('post', 'relatedPosts', 'recentPosts'));
    }

    public function category(BlogCategory $category)
    {
        $posts = $category->posts()
            ->published()
            ->with(['author'])
            ->latest()
            ->paginate(12);
        
        return view('blog.category', compact('category', 'posts'));
    }

    public function author($authorId)
    {
        $author = \App\Models\User::findOrFail($authorId);
        
        $posts = BlogPost::published()
            ->where('author_id', $authorId)
            ->with(['category'])
            ->latest()
            ->paginate(12);
        
        return view('blog.author', compact('author', 'posts'));
    }

    public function search(Request $request)
    {
        $request->validate([
            'q' => 'required|string|min:3',
        ]);
        
        $query = $request->q;
        
        $posts = BlogPost::published()
            ->where(function($q) use ($query) {
                $q->where('title', 'like', '%' . $query . '%')
                  ->orWhere('content', 'like', '%' . $query . '%')
                  ->orWhere('excerpt', 'like', '%' . $query . '%');
            })
            ->with(['author', 'category'])
            ->latest()
            ->paginate(12);
        
        return view('blog.search', compact('posts', 'query'));
    }
}
