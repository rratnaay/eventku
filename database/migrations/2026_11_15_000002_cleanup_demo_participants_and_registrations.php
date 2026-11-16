<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

return new class extends Migration
{
    public function up(): void
    {
        DB::transaction(function () {
            $demoUserIds = DB::table('users')
                ->where('role', '!=', 'admin')
                ->where('name', 'like', 'Peserta Demo %')
                ->where('email', 'like', 'participant%@eventku.test')
                ->pluck('id');

            $demoRegistrationsQuery = DB::table('registrations')
                ->where(function ($query) use ($demoUserIds) {
                    $query->whereIn('user_id', $demoUserIds)
                        ->orWhere(function ($q) {
                            $q->whereNull('participant_name')
                                ->whereIn('user_id', function ($sub) {
                                    $sub->select('id')
                                        ->from('users')
                                        ->where('email', 'like', '%@eventku.test');
                                });
                        });
                });

            $proofs = (clone $demoRegistrationsQuery)
                ->whereNotNull('payment_proof')
                ->pluck('payment_proof');

            foreach ($proofs as $proof) {
                Storage::disk(config('filesystems.poster_disk'))->delete($proof);
            }

            $demoRegistrationsQuery->delete();

            if ($demoUserIds->isNotEmpty()) {
                DB::table('sessions')->whereIn('user_id', $demoUserIds)->delete();
                DB::table('users')->whereIn('id', $demoUserIds)->delete();
            }
        });
    }

    public function down(): void
    {
        // Pembersihan data demo bersifat satu arah.
    }
};
