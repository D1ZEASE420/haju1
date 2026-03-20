<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Album extends Model
{
    protected $table = 'my_favorite_subject';

    protected $fillable = [
        'user_id',
        'title',
        'image',
        'description',
        'artist',
        'release_year',
        'genre',
        'rating',
    ];

    protected $casts = [
        'release_year' => 'integer',
        'rating'       => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}