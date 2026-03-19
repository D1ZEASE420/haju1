<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Blog;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    // Store a new comment
    public function store(Request $request, Blog $blog)
    {
        $request->validate([
            'content' => 'required|string|max:1000',
        ]);

        $blog->comments()->create([
            'user_id' => $request->user()->id,
            'content' => $request->input('content'),
        ]);

        return redirect()->back();
    }

    // Delete a comment
    public function destroy(Comment $comment)
    {
        $comment->delete();
        return redirect()->back();
    }
}