<?php

namespace App\Http\Controllers;

use App\Models\Blog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class BlogController extends Controller
{
    public function index()
    {
        $blogs = Blog::with('user:id,name')
            ->withCount('comments')
            ->orderByDesc('created_at')
            ->get();

        return Inertia::render('Blogs/Index', [
            'blogs' => $blogs,
        ]);
    }

    public function create()
    {
        return Inertia::render('Blogs/Create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'required|string',
        ]);

        $data['user_id'] = Auth::id();

        Blog::create($data);

        return redirect()->route('blogs.index');
    }

    public function show(Blog $blog)
    {
        $blog->load([
            'user:id,name',
            'comments' => fn ($q) => $q->orderByDesc('created_at'),
            'comments.user:id,name,is_admin',
        ]);

        return Inertia::render('Blogs/Show', [
            'blog' => $blog,
        ]);
    }

    public function edit(Blog $blog)
    {
        $this->authorizeAccess($blog);

        return Inertia::render('Blogs/Edit', [
            'blog' => $blog,
        ]);
    }

    public function update(Request $request, Blog $blog)
    {
        $this->authorizeAccess($blog);

        $data = $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'required|string',
        ]);

        $blog->update($data);

        return redirect()->route('blogs.index');
    }

    public function destroy(Blog $blog)
    {
        $this->authorizeAccess($blog);

        $blog->delete();

        return redirect()->route('blogs.index');
    }

    // -------------------------------------------------------------------------

    private function authorizeAccess(Blog $blog): void
    {
        $user = Auth::user();

        if ($user->isAdmin()) return;

        abort_if($blog->user_id !== $user->id, 403, 'Puudub õigus.');
    }
}