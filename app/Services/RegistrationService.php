<?php

namespace App\Services;

use App\Exceptions\RegistrationException;
use App\Models\Event;
use App\Models\Registration;
use App\Models\User;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;

class RegistrationService
{
    /**
     * Mendaftarkan user ke event secara atomik.
     *
     * Baris event dikunci (SELECT ... FOR UPDATE) selama transaksi sehingga dua
     * pendaftaran bersamaan tidak bisa sama-sama melewati batas kuota.
     * Unique index (user_id, event_id) menjadi pengaman terakhir terhadap pendaftaran ganda.
     *
     * @throws RegistrationException
     */
    public function register(User $user, Event $event): Registration
    {
        try {
            return DB::transaction(function () use ($user, $event) {
                $locked = Event::query()->lockForUpdate()->findOrFail($event->id);

                $existing = Registration::query()
                    ->where('user_id', $user->id)
                    ->where('event_id', $locked->id)
                    ->lockForUpdate()
                    ->first();

                if ($existing && ! $existing->isCancelled()) {
                    throw new RegistrationException('Anda sudah terdaftar pada event ini.');
                }

                if ($reason = $locked->closedReason()) {
                    throw new RegistrationException($reason);
                }

                // Pendaftaran yang pernah dibatalkan dihidupkan kembali (unique index tetap terjaga).
                if ($existing) {
                    $existing->update([
                        'status' => Registration::STATUS_CONFIRMED,
                        'registered_at' => now(),
                    ]);

                    return $existing->refresh();
                }

                return Registration::create([
                    'user_id' => $user->id,
                    'event_id' => $locked->id,
                    'code' => Registration::generateCode(),
                    'status' => Registration::STATUS_CONFIRMED,
                    'registered_at' => now(),
                ]);
            });
        } catch (UniqueConstraintViolationException) {
            throw new RegistrationException('Anda sudah terdaftar pada event ini.');
        }
    }

    /**
     * Membatalkan pendaftaran (kuota otomatis kembali karena hanya menghitung status non-cancelled).
     *
     * @throws RegistrationException
     */
    public function cancel(Registration $registration): Registration
    {
        return DB::transaction(function () use ($registration) {
            $locked = Registration::query()->lockForUpdate()->findOrFail($registration->id);

            if ($locked->isCancelled()) {
                throw new RegistrationException('Pendaftaran ini sudah dibatalkan.');
            }

            if ($locked->status === Registration::STATUS_ATTENDED) {
                throw new RegistrationException('Pendaftaran yang sudah ditandai hadir tidak dapat dibatalkan.');
            }

            if ($locked->event->ends_at->isPast()) {
                throw new RegistrationException('Event sudah selesai, pendaftaran tidak dapat dibatalkan.');
            }

            $locked->update(['status' => Registration::STATUS_CANCELLED]);

            return $locked;
        });
    }
}
