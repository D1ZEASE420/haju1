<?php

namespace App\Http\Controllers;

use App\Models\Blog;
use Illuminate\Http\Request;
use Inertia\Inertia;

class BlogController extends Controller
{
    public function index()
    {
        // Fetch all blog posts, newest first
        $blogs = Blog::orderBy('created_at', 'desc')->get();

        // Return Inertia page
        return Inertia::render('Blogs/Index', [
            'blogs' => $blogs
        ]);
    }

    public function create()
    {
        return Inertia::render('Blogs/Create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
        ]);

        $blog = Blog::create($request->only('title', 'description'));

        return redirect()->route('blogs.index');
    }

    public function edit(Blog $blog)
    {
        return Inertia::render('Blogs/Edit', [
            'blog' => $blog
        ]);
    }

    public function update(Request $request, Blog $blog)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
        ]);

        $blog->update($request->only('title', 'description'));

        return redirect()->route('blogs.index');
    }

    public function show(Blog $blog)
    {
        $blog->load('comments.user'); // eager load comments and their authors
        return inertia('Blogs/Show', [
            'blog' => $blog
        ]);
    }
}