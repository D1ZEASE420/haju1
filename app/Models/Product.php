<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    protected $fillable = [
        'name',
        'description',
        'price',
        'image_url',
        'category',
        'in_stock',
    ];

    protected $casts = [
        'price'    => 'float',
        'in_stock' => 'boolean',
    ];
}