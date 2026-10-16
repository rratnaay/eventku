<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Registration extends Model
{
    use HasFactory;

    public const STATUS_CONFIRMED = 'confirmed';
    public const STATUS_ATTENDED = 'attended';
    public const STATUS_CANCELLED = 'cancelled';

    public const STATUSES = [
        self::STATUS_CONFIRMED => 'Terkonfirmasi',
        self::STATUS_ATTENDED => 'Hadir',
        self::STATUS_CANCELLED => 'Dibatalkan',
    ];

    protected $fillable = ['user_id', 'event_id', 'code', 'status', 'registered_at'];

    protected function casts(): array
    {
        return ['registered_at' => 'datetime'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status] ?? ucfirst($this->status);
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_CONFIRMED => 'green',
            self::STATUS_ATTENDED => 'blue',
            default => 'red',
        };
    }

    public function isCancelled(): bool
    {
        return $this->status === self::STATUS_CANCELLED;
    }

    public static function generateCode(): string
    {
        do {
            $code = 'EVK-'.strtoupper(\Illuminate\Support\Str::random(8));
        } while (static::where('code', $code)->exists());

        return $code;
    }
}
