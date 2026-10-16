<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Event extends Model
{
    use HasFactory;

    public const STATUS_DRAFT = 'draft';
    public const STATUS_PUBLISHED = 'published';
    public const STATUS_CLOSED = 'closed';
    public const STATUS_CANCELLED = 'cancelled';

    public const STATUSES = [
        self::STATUS_DRAFT => 'Draft',
        self::STATUS_PUBLISHED => 'Dipublikasikan',
        self::STATUS_CLOSED => 'Pendaftaran Ditutup',
        self::STATUS_CANCELLED => 'Dibatalkan',
    ];

    protected $fillable = [
        'category_id', 'title', 'slug', 'description', 'poster', 'organizer',
        'location', 'city', 'starts_at', 'ends_at', 'quota', 'price',
        'status', 'is_featured',
    ];

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'quota' => 'integer',
            'price' => 'integer',
            'is_featured' => 'boolean',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /* ---------- Relasi ---------- */

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function registrations(): HasMany
    {
        return $this->hasMany(Registration::class);
    }

    /* ---------- Scopes ---------- */

    /** Event yang boleh dilihat publik. */
    public function scopeVisible(Builder $query): Builder
    {
        return $query->whereIn('status', [self::STATUS_PUBLISHED, self::STATUS_CLOSED]);
    }

    public function scopeWithSeatCount(Builder $query): Builder
    {
        return $query->withCount([
            'registrations as active_registrations_count' => fn ($q) => $q->where('status', '!=', Registration::STATUS_CANCELLED),
        ]);
    }

    public function scopeUpcoming(Builder $query): Builder
    {
        return $query->where('ends_at', '>=', now());
    }

    public function scopeFinished(Builder $query): Builder
    {
        return $query->where('ends_at', '<', now());
    }

    public function scopeFull(Builder $query): Builder
    {
        return $query->whereRaw(
            'quota <= (select count(*) from registrations where registrations.event_id = events.id and registrations.status != ?)',
            [Registration::STATUS_CANCELLED]
        );
    }

    /* ---------- Accessor & helper ---------- */

    public function getPosterUrlAttribute(): string
    {
        if (! $this->poster) {
            return asset('images/hero.jpg');
        }

        if (str_starts_with($this->poster, 'images/')) {
            return asset($this->poster);
        }

        return Storage::disk('public')->url($this->poster);
    }

    public function getActiveRegistrationsAttribute(): int
    {
        return $this->active_registrations_count
            ?? $this->registrations()->where('status', '!=', Registration::STATUS_CANCELLED)->count();
    }

    public function getSeatsLeftAttribute(): int
    {
        return max(0, $this->quota - $this->active_registrations);
    }

    public function getIsFullAttribute(): bool
    {
        return $this->seats_left <= 0;
    }

    public function getIsFinishedAttribute(): bool
    {
        return $this->ends_at->isPast();
    }

    public function getIsFreeAttribute(): bool
    {
        return $this->price === 0;
    }

    public function getPriceLabelAttribute(): string
    {
        return $this->is_free ? 'Gratis' : 'Rp '.number_format($this->price, 0, ',', '.');
    }

    /** Alasan pendaftaran tidak dapat dilakukan, atau null jika terbuka. */
    public function closedReason(): ?string
    {
        return match (true) {
            $this->status === self::STATUS_CANCELLED => 'Event ini dibatalkan.',
            $this->status === self::STATUS_DRAFT => 'Event ini belum dipublikasikan.',
            $this->status === self::STATUS_CLOSED => 'Pendaftaran untuk event ini sudah ditutup.',
            $this->is_finished => 'Event ini sudah selesai.',
            $this->is_full => 'Kuota event ini sudah penuh.',
            default => null,
        };
    }

    public function getIsOpenAttribute(): bool
    {
        return $this->closedReason() === null;
    }

    /** Label & warna status untuk tampilan. */
    public function getDisplayStatusAttribute(): array
    {
        return match (true) {
            $this->status === self::STATUS_CANCELLED => ['Dibatalkan', 'red'],
            $this->status === self::STATUS_DRAFT => ['Draft', 'gray'],
            $this->is_finished => ['Selesai', 'gray'],
            $this->status === self::STATUS_CLOSED => ['Ditutup', 'amber'],
            $this->is_full => ['Penuh', 'amber'],
            $this->starts_at->isPast() => ['Berlangsung', 'blue'],
            default => ['Terbuka', 'green'],
        };
    }

    public function getScheduleLabelAttribute(): string
    {
        $start = Carbon::parse($this->starts_at)->locale('id');
        $end = Carbon::parse($this->ends_at)->locale('id');

        if ($start->isSameDay($end)) {
            return $start->translatedFormat('l, d F Y').' · '.$start->format('H:i').' – '.$end->format('H:i').' WIB';
        }

        return $start->translatedFormat('d M Y H:i').' – '.$end->translatedFormat('d M Y H:i').' WIB';
    }
}
