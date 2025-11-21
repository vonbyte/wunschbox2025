<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Wish extends Model
{
    /** @use HasFactory<\Database\Factories\WishFactory> */
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'image',
        'receiver',
        'example_links',
        'is_public',
    ];

    protected function casts(): array
    {
        return [
            'example_links' => 'array',
            'is_public' => 'boolean',
        ];
    }
}
