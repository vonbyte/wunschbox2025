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

    /**
     * People guests can make a wish for
     */
    const RECEIVERS = ['Sonia', 'Tanja'];

    protected $fillable = [
        'title',
        'description',
        'image',
        'image_thumbnail',
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
            'sortnr' => 'integer',
        ];
    }

    /**
     * The person whose wish note the public Wunschliste is. Only her public wishes may ever show there.
     */
    public static function owner(): string
    {
        return config('wishbox.owner');
    }

    /**
     * Scope: Wishes on the owner's public wish note
     */
    public function scopeOnWishNote($query)
    {
        return $query->where('is_public', true)->where('receiver', self::owner());
    }

    /**
     * Scope: Everything else, including public wishes of other receivers
     */
    public function scopeNotOnWishNote($query)
    {
        return $query->where(fn ($query) => $query
            ->where('is_public', false)
            ->orWhere('receiver', '!=', self::owner()));
    }

    public function isOnWishNote(): bool
    {
        return $this->is_public && $this->belongsToOwner();
    }

    public function belongsToOwner(): bool
    {
        return $this->receiver === self::owner();
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
            self::STATUS_UNPACKED => 'purple',
            default => 'gray',
        };
    }

    /**
     * Scope: Order by sortnr then created_at
     */
    public function scopeSorted($query)
    {
        return $query->orderBy('sortnr', 'asc')->orderBy('created_at', 'desc');
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
            ->orderBy('sortnr', 'asc')
            ->orderBy('created_at', 'desc');
    }

    public function scopeUnpacked($query)
    {
        return $query->where('status', 'unpacked')
            ->orderBy('sortnr', 'desc')
            ->orderBy('created_at', 'desc');
    }

    /**
     * Scope: Filter by Status where status is not delivered, packed or unpacked
     */
    public function scopeOpen($query)
    {
        return $query->whereIn('status', ['open', 'idea', 'ordered'])
            ->orderBy('created_at', 'desc');
    }

}
