<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Blog extends Model
{
    protected $fillable = ['title', 'description'];

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }
}