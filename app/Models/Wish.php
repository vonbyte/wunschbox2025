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

    const STATUS_PACKED = 'packed';

    const STATUS_UNPACKED = 'unpacked';

    protected $fillable = [
        'title',
        'description',
        'image',
        'receiver',
        'example_links',
        'is_public',
        'status',
        'sortnr',
        'package_nr',
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
            self::STATUS_PACKED => 'Eingepackt',
            self::STATUS_UNPACKED => 'Ausgepackt',
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
        return match ($this->status) {
            self::STATUS_IDEA => 'gray',
            self::STATUS_OPEN => 'blue',
            self::STATUS_ORDERED => 'yellow',
            self::STATUS_DELIVERED => 'orange',
            self::STATUS_PACKED => 'green',
            self::STATUS_UNPACKED => 'brown',
            default => 'gray',
        };
    }

    /**
     * Scope: Order by sort_nr then created_at
     */
    public function scopeSorted($query)
    {
        return $query->orderBy('sort_nr', 'asc')->orderBy('created_at', 'desc');
    }

    /**
     * Scope: Filter by Status and sort by created_at
     */
    public function scopeDelivered($query)
    {
        return $query->where('status', 'delivered')
            ->orderBy('created_at', 'desc');
    }

    public function scopePacked($query)
    {
        return $query->where('status', 'packed')
            ->orderBy('sort_nr', 'asc')
            ->orderBy('created_at', 'desc');
    }

    public function scopeUnpacked($query)
    {
        return $query->where('status', 'unpacked')
            ->orderBy('sort_nr', 'desc')
            ->orderBy('created_at', 'desc');
    }

    /**
     * Scope: Filter by Status where status is not delivered, packed or unpacked
     */
    public function scopeOpen($query)
    {
        return $query->where('status', 'open')
            ->where('status', 'idea')
            ->where('status', 'ordered')
            ->orderBy('created_at', 'desc');
    }

}
