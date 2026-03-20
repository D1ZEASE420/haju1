<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Blog extends Model
{
    protected $fillable = ['user_id', 'title', 'description'];

    /**
     * The user who authored this blog post.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Comments on this blog post.
     */
    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }
}