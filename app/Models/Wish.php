<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Wish extends Model
{
    /** @use HasFactory<\Database\Factories\WishFactory> */
    use HasFactory;

    const STATUS_IDEA = 'idea';
    const STATUS_OPEN = 'open';
    const STATUS_ORDERED = 'ordered';
    const STATUS_DELIVERED = 'delivered';
    protected $fillable = [
        'title',
        'description',
        'image',
        'receiver',
        'example_links',
        'is_public',
        'status',
        'sortnr'
    ];

    protected function casts(): array
    {
        return [
            'example_links' => 'array',
            'is_public' => 'boolean',
        ];
    }

    /**
     * Get all available statuses
     */
    public static function getStatuses(): array
    {
        return [
            self::STATUS_IDEA => 'Idee',
            self::STATUS_OPEN => 'Offen',
            self::STATUS_ORDERED => 'Bestellt',
            self::STATUS_DELIVERED => 'Geliefert',
        ];
    }

    /**
     * Get status label
     */
    public function getStatusLabelAttribute(): string
    {
        return self::getStatuses()[$this->status] ?? 'Unbekannt';
    }


    /**
     * Get status color for badges
     */
    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            self::STATUS_IDEA => 'gray',
            self::STATUS_OPEN => 'blue',
            self::STATUS_ORDERED => 'yellow',
            self::STATUS_DELIVERED => 'green',
            default => 'gray',
        };
    }

    /**
     * Scope: Order by sort_nr then created_at
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_nr', 'asc')->orderBy('created_at', 'desc');
    }
}
