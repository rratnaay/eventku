<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Registration extends Model
{
    use HasFactory;

    public const STATUS_PENDING = 'pending';

    public const STATUS_CONFIRMED = 'confirmed';

    public const STATUS_ATTENDED = 'attended';

    public const STATUS_CANCELLED = 'cancelled';

    public const STATUSES = [
        self::STATUS_PENDING => 'Menunggu Verifikasi',
        self::STATUS_CONFIRMED => 'Terkonfirmasi',
        self::STATUS_ATTENDED => 'Hadir',
        self::STATUS_CANCELLED => 'Dibatalkan',
    ];

    public const PAYMENT_UNPAID = 'unpaid';

    public const PAYMENT_WAITING_VERIFICATION = 'waiting_verification';

    public const PAYMENT_PAID = 'paid';

    public const PAYMENT_FREE = 'free';

    public const PAYMENT_METHODS = [
        'transfer_bank' => 'Transfer Bank (BCA / Mandiri / BNI)',
        'qris' => 'QRIS (Semua E-Wallet & Mobile Banking)',
        'ewallet' => 'E-Wallet (GoPay / OVO / DANA)',
    ];

    protected $fillable = [
        'user_id',
        'event_id',
        'code',
        'participant_name',
        'participant_email',
        'participant_phone',
        'participant_institution',
        'participant_city',
        'notes',
        'status',
        'payment_method',
        'payment_status',
        'payment_amount',
        'payment_proof',
        'paid_at',
        'verified_at',
        'registered_at',
    ];

    protected function casts(): array
    {
        return [
            'registered_at' => 'datetime',
            'paid_at' => 'datetime',
            'verified_at' => 'datetime',
            'payment_amount' => 'integer',
        ];
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
        if ($this->status === self::STATUS_PENDING) {
            if ($this->payment_status === self::PAYMENT_UNPAID && ! $this->payment_proof && $this->payment_amount > 0) {
                return 'Menunggu Pembayaran';
            }

            return 'Menunggu Verifikasi';
        }

        return self::STATUSES[$this->status] ?? ucfirst($this->status);
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_CONFIRMED => 'green',
            self::STATUS_ATTENDED => 'blue',
            self::STATUS_PENDING => 'amber',
            default => 'red',
        };
    }

    public function getPaymentMethodLabelAttribute(): string
    {
        if ($this->payment_method === 'free' || ($this->payment_amount === 0 && ! $this->payment_method)) {
            return 'Gratis (Tanpa Pembayaran)';
        }

        return self::PAYMENT_METHODS[$this->payment_method] ?? ucfirst((string) $this->payment_method);
    }

    public function getPaymentStatusLabelAttribute(): string
    {
        return match ($this->payment_status) {
            self::PAYMENT_FREE => 'Gratis',
            self::PAYMENT_PAID => 'Lunas & Terverifikasi',
            self::PAYMENT_WAITING_VERIFICATION => 'Menunggu Verifikasi Admin',
            default => 'Menunggu Pembayaran',
        };
    }

    public function getPaymentProofUrlAttribute(): ?string
    {
        if (! $this->payment_proof) {
            return null;
        }

        return Storage::disk(config('filesystems.poster_disk'))->url($this->payment_proof);
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isConfirmed(): bool
    {
        return in_array($this->status, [self::STATUS_CONFIRMED, self::STATUS_ATTENDED], true);
    }

    public function isCancelled(): bool
    {
        return $this->status === self::STATUS_CANCELLED;
    }

    public static function generateCode(): string
    {
        do {
            $code = 'EVK-'.strtoupper(Str::random(8));
        } while (static::where('code', $code)->exists());

        return $code;
    }
}
