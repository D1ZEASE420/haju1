<?php

namespace App\Http\Controllers;

use App\Models\Blog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class BlogController extends Controller
{
    /**
     * List all blog posts, newest first.
     * Includes comment count and author name for the index view.
     */
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

    /**
     * Show the create form.
     */
    public function create()
    {
        return Inertia::render('Blogs/Create');
    }

    /**
     * Store a new blog post, owned by the authenticated user.
     */
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

    /**
     * Show a single post with its comments and authors.
     */
    public function show(Blog $blog)
    {
        $blog->load([
            'user:id,name',
            'comments' => fn ($q) => $q->orderByDesc('created_at'),
            'comments.user:id,name',
        ]);

        return Inertia::render('Blogs/Show', [
            'blog' => $blog,
        ]);
    }

    /**
     * Show the edit form — only the author or an admin may edit.
     */
    public function edit(Blog $blog)
    {
        $this->authorizeAccess($blog);

        return Inertia::render('Blogs/Edit', [
            'blog' => $blog,
        ]);
    }

    /**
     * Update the blog post — only the author or an admin may update.
     */
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

    /**
     * Delete the blog post — only the author or an admin may delete.
     * Comments are deleted automatically via cascade.
     */
    public function destroy(Blog $blog)
    {
        $this->authorizeAccess($blog);

        $blog->delete();

        return redirect()->route('blogs.index');
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    /**
     * Abort with 403 unless the current user owns the post or is an admin.
     * "Admin" is defined as a user whose email matches ADMIN_EMAIL in .env,
     * or whose id === 1 (simple fallback).
     */
    private function authorizeAccess(Blog $blog): void
    {
        $user = Auth::user();

        if ($this->isAdmin($user)) {
            return; // admins can do anything
        }

        abort_if($blog->user_id !== $user->id, 403, 'This action is not authorised.');
    }

    /**
     * Determine whether the given user is an admin.
     * Adjust this logic to match your application's admin strategy.
     */
    public static function isAdmin($user): bool
    {
        if (!$user) return false;

        // Option 1 – match a specific e-mail set in .env
        $adminEmail = config('app.admin_email', env('ADMIN_EMAIL'));
        if ($adminEmail && $user->email === $adminEmail) {
            return true;
        }

        // Option 2 – first registered user (id === 1) is admin
        return $user->id === 1;
    }
}