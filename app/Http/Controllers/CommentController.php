<?php

namespace App\Http\Controllers;

use App\Models\Blog;
use App\Models\Comment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CommentController extends Controller
{
    /**
     * Store a new comment.
     * Any authenticated user may comment.
     */
    public function store(Request $request, Blog $blog)
    {
        $data = $request->validate([
            'content' => 'required|string|max:1000',
        ]);

        $blog->comments()->create([
            'user_id' => Auth::id(),
            'content' => $data['content'],
        ]);

        return redirect()->back();
    }

    /**
     * Update a comment.
     * Only the comment author may edit their own comment.
     */
    public function update(Request $request, Comment $comment)
    {
        $user = Auth::user();

        abort_if($comment->user_id !== $user->id, 403, 'Saad muuta ainult oma kommentaare.');

        $data = $request->validate([
            'content' => 'required|string|max:1000',
        ]);

        $comment->update(['content' => $data['content']]);

        return redirect()->back();
    }

    /**
     * Delete a comment.
     * Allowed for:
     *   - admin (can delete any comment)
     *   - blog post owner (can moderate comments on their post)
     *   - comment author (can delete their own comment)
     */
    public function destroy(Comment $comment)
    {
        $user = Auth::user();

        $isAdmin     = $user->isAdmin();
        $isAuthor    = $comment->user_id === $user->id;
        $isBlogOwner = $comment->blog && $comment->blog->user_id === $user->id;

        abort_if(! $isAdmin && ! $isAuthor && ! $isBlogOwner, 403, 'Puudub õigus.');

        $comment->delete();

        return redirect()->back();
    }
}