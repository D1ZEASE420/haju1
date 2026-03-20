<?php

namespace App\Http\Controllers;

use App\Models\Blog;
use App\Models\Comment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CommentController extends Controller
{
    /**
     * Store a new comment on a blog post.
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
     * Delete a comment.
     * Allowed if:
     *   - the current user is the comment author, OR
     *   - the current user is an admin (BlogController::isAdmin), OR
     *   - the current user is the owner of the blog post the comment belongs to.
     */
    public function destroy(Comment $comment)
    {
        $user = Auth::user();

        $isAdmin       = BlogController::isAdmin($user);
        $isAuthor      = $comment->user_id === $user->id;
        $isBlogOwner   = $comment->blog && $comment->blog->user_id === $user->id;

        abort_if(!$isAdmin && !$isAuthor && !$isBlogOwner, 403, 'This action is not authorised.');

        $comment->delete();

        return redirect()->back();
    }
}