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

    public const CATEGORY_POSTERS = [
        'musik' => [
            'images/konser 1.jpg',
            'images/konser 2.jpg',
            'images/konser 3.jpg',
            'images/konser 4.jpg',
            'images/poster-music.jpg',
        ],
        'kuliner' => [
            'images/kuliner 1.jpg',
            'images/kuliner 2.jpg',
            'images/kuliner 3.jpg',
        ],
        'olahraga' => [
            'images/oalahraga 1.jpg',
            'images/olahraga 2.jpg',
            'images/poster-sport.jpg',
        ],
        'pendidikan' => [
            'images/pendidikan 1.jpg',
            'images/pendidikan 2.jpg',
            'images/pendidikan 3.jpg',
        ],
        'seni' => [
            'images/seni 1.jpg',
            'images/seni 2.jpg',
            'images/poster-art.jpg',
        ],
        'teknologi' => [
            'images/poster-tech.jpg',
        ],
    ];

    public function getPosterUrlAttribute(): string
    {
        if ($this->poster) {
            if (str_starts_with($this->poster, 'images/')) {
                if (file_exists(public_path($this->poster))) {
                    return asset($this->poster);
                }

                return asset($this->defaultPosterByCategory());
            }

            return Storage::disk(config('filesystems.poster_disk'))->url($this->poster);
        }

        return asset($this->defaultPosterByCategory());
    }

    public function defaultPosterByCategory(): string
    {
        $category = $this->relationLoaded('category') ? $this->category : $this->category()->first();
        $slug = strtolower($category?->slug ?? '');
        $name = strtolower($category?->name ?? '');
        $icon = $category?->icon ?? '';

        $key = match (true) {
            str_contains($slug, 'musik') || str_contains($name, 'musik') || str_contains($name, 'konser') || $icon === 'music' => 'musik',
            str_contains($slug, 'kuliner') || str_contains($name, 'kuliner') || $icon === 'cup' => 'kuliner',
            str_contains($slug, 'olahraga') || str_contains($name, 'olahraga') || $icon === 'run' => 'olahraga',
            str_contains($slug, 'pendidikan') || str_contains($name, 'pendidikan') || $icon === 'book' => 'pendidikan',
            str_contains($slug, 'seni') || str_contains($name, 'seni') || $icon === 'palette' => 'seni',
            str_contains($slug, 'teknologi') || str_contains($name, 'teknologi') || $icon === 'cpu' => 'teknologi',
            default => null,
        };

        if ($key && isset(self::CATEGORY_POSTERS[$key])) {
            $posters = self::CATEGORY_POSTERS[$key];
            $index = $this->id ? (($this->id - 1) % count($posters)) : 0;

            return $posters[$index];
        }

        return 'images/poster-music.jpg';
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
