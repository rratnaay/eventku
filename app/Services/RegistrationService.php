<?php

namespace App\Services;

use App\Exceptions\RegistrationException;
use App\Models\Event;
use App\Models\Registration;
use App\Models\User;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class RegistrationService
{
    /**
     * Mendaftarkan user ke event secara atomik menggunakan data profil dan informasi pembayaran.
     *
     * Baris event dikunci (SELECT ... FOR UPDATE) selama transaksi sehingga dua
     * pendaftaran bersamaan tidak bisa sama-sama melewati batas kuota.
     * Unique index (user_id, event_id) menjadi pengaman terakhir terhadap pendaftaran ganda.
     *
     * @param  array<string, mixed>  $payload
     *
     * @throws RegistrationException
     */
    public function register(User $user, Event $event, array $payload = []): Registration
    {
        try {
            return DB::transaction(function () use ($user, $event, $payload) {
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

                $participantName = $payload['name'] ?? $user->name;
                $participantEmail = $payload['email'] ?? $user->email;
                $participantPhone = $payload['phone'] ?? $user->phone;
                $participantInstitution = $payload['institution'] ?? $user->institution;
                $participantCity = $payload['city'] ?? $user->city;
                $notes = $payload['notes'] ?? null;

                // Simpan pembaruan data profil user agar tidak perlu mengisi ulang di pendaftaran berikutnya.
                $profileUpdates = array_filter([
                    'name' => $participantName,
                    'phone' => $participantPhone,
                    'institution' => $participantInstitution,
                    'city' => $participantCity,
                ], fn ($val) => $val !== null && $val !== '');

                if ($profileUpdates !== []) {
                    $user->fill($profileUpdates)->save();
                }

                $isFree = $locked->is_free;
                $paymentProof = $payload['payment_proof_path'] ?? null;
                $paymentMethod = $isFree
                    ? 'free'
                    : ($payload['payment_method'] ?? 'transfer_bank');

                $paymentStatus = match (true) {
                    $isFree => Registration::PAYMENT_FREE,
                    (bool) $paymentProof => Registration::PAYMENT_WAITING_VERIFICATION,
                    default => Registration::PAYMENT_UNPAID,
                };

                $attributes = [
                    'participant_name' => $participantName,
                    'participant_email' => $participantEmail,
                    'participant_phone' => $participantPhone,
                    'participant_institution' => $participantInstitution,
                    'participant_city' => $participantCity,
                    'notes' => $notes,
                    'status' => Registration::STATUS_PENDING,
                    'payment_method' => $paymentMethod,
                    'payment_status' => $paymentStatus,
                    'payment_amount' => $locked->price,
                    'payment_proof' => $paymentProof,
                    'paid_at' => ($isFree || $paymentProof) ? now() : null,
                    'verified_at' => null,
                    'registered_at' => now(),
                ];

                // Pendaftaran yang pernah dibatalkan dihidupkan kembali (unique index tetap terjaga).
                if ($existing) {
                    if ($existing->payment_proof && $paymentProof && $existing->payment_proof !== $paymentProof) {
                        Storage::disk(config('filesystems.poster_disk'))->delete($existing->payment_proof);
                    }
                    $existing->update($attributes);

                    return $existing->refresh();
                }

                return Registration::create(array_merge([
                    'user_id' => $user->id,
                    'event_id' => $locked->id,
                    'code' => Registration::generateCode(),
                ], $attributes));
            });
        } catch (UniqueConstraintViolationException) {
            throw new RegistrationException('Anda sudah terdaftar pada event ini.');
        }
    }

    /**
     * Mengunggah bukti pembayaran untuk pendaftaran yang masih menunggu pembayaran/verifikasi.
     *
     * @throws RegistrationException
     */
    public function uploadPaymentProof(Registration $registration, string $proofPath, ?string $paymentMethod = null): Registration
    {
        return DB::transaction(function () use ($registration, $proofPath, $paymentMethod) {
            $locked = Registration::query()->lockForUpdate()->findOrFail($registration->id);

            if ($locked->isCancelled()) {
                throw new RegistrationException('Pendaftaran yang sudah dibatalkan tidak dapat diperbarui.');
            }

            if ($locked->isConfirmed()) {
                throw new RegistrationException('Pembayaran untuk pendaftaran ini sudah terverifikasi.');
            }

            if ($locked->payment_proof && $locked->payment_proof !== $proofPath) {
                Storage::disk(config('filesystems.poster_disk'))->delete($locked->payment_proof);
            }

            $locked->update([
                'payment_method' => $paymentMethod ?: ($locked->payment_method ?: 'transfer_bank'),
                'payment_proof' => $proofPath,
                'payment_status' => Registration::PAYMENT_WAITING_VERIFICATION,
                'paid_at' => now(),
            ]);

            return $locked->refresh();
        });
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
