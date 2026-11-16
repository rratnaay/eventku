<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'institution')) {
                $table->string('institution', 120)->nullable()->after('phone');
            }
            if (! Schema::hasColumn('users', 'city')) {
                $table->string('city', 80)->nullable()->after('institution');
            }
        });

        Schema::table('registrations', function (Blueprint $table) {
            if (! Schema::hasColumn('registrations', 'participant_name')) {
                $table->string('participant_name', 100)->nullable()->after('code');
            }
            if (! Schema::hasColumn('registrations', 'participant_email')) {
                $table->string('participant_email', 150)->nullable()->after('participant_name');
            }
            if (! Schema::hasColumn('registrations', 'participant_phone')) {
                $table->string('participant_phone', 30)->nullable()->after('participant_email');
            }
            if (! Schema::hasColumn('registrations', 'participant_institution')) {
                $table->string('participant_institution', 120)->nullable()->after('participant_phone');
            }
            if (! Schema::hasColumn('registrations', 'participant_city')) {
                $table->string('participant_city', 80)->nullable()->after('participant_institution');
            }
            if (! Schema::hasColumn('registrations', 'notes')) {
                $table->text('notes')->nullable()->after('participant_city');
            }
            if (! Schema::hasColumn('registrations', 'payment_method')) {
                $table->string('payment_method', 40)->nullable()->after('status');
            }
            if (! Schema::hasColumn('registrations', 'payment_status')) {
                $table->string('payment_status', 30)->default('unpaid')->after('payment_method');
            }
            if (! Schema::hasColumn('registrations', 'payment_amount')) {
                $table->unsignedInteger('payment_amount')->default(0)->after('payment_status');
            }
            if (! Schema::hasColumn('registrations', 'payment_proof')) {
                $table->string('payment_proof')->nullable()->after('payment_amount');
            }
            if (! Schema::hasColumn('registrations', 'paid_at')) {
                $table->timestamp('paid_at')->nullable()->after('payment_proof');
            }
            if (! Schema::hasColumn('registrations', 'verified_at')) {
                $table->timestamp('verified_at')->nullable()->after('paid_at');
            }
        });

        // Sinkronisasi status pembayaran untuk pendaftaran yang sudah terkonfirmasi sebelumnya.
        DB::table('registrations')
            ->whereIn('status', ['confirmed', 'attended'])
            ->update(['payment_status' => 'paid']);

        // Perbarui poster event bawaan sesuai kategori tanpa menghapus data.
        $posterMap = [
            'senja-di-kota-fest-2026' => 'images/konser 1.jpg',
            'konser-akustik-malam-minggu' => 'images/konser 2.jpg',
            'intimate-jazz-night' => 'images/konser 3.jpg',
            'laravel-jakarta-summit' => 'images/poster-tech.jpg',
            'workshop-uiux-untuk-pemula' => 'images/poster-tech.jpg',
            'workshop-ui-ux-untuk-pemula' => 'images/poster-tech.jpg',
            'hackathon-kota-cerdas' => 'images/poster-tech.jpg',
            'konferensi-startup-muda' => 'images/poster-tech.jpg',
            'pameran-batik-nusantara' => 'images/seni 1.jpg',
            'malam-puisi-musikalisasi' => 'images/seni 2.jpg',
            'sunrise-fun-run-5k' => 'images/oalahraga 1.jpg',
            'yoga-mindfulness-pagi' => 'images/olahraga 2.jpg',
            'festival-layang-layang-pantai' => 'images/poster-sport.jpg',
            'bazar-kuliner-nusantara' => 'images/kuliner 1.jpg',
            'workshop-memasak-masakan-padang' => 'images/kuliner 2.jpg',
            'seminar-karier-fresh-graduate' => 'images/pendidikan 1.jpg',
            'pekan-bahasa-inggris-kampus' => 'images/pendidikan 2.jpg',
        ];

        foreach ($posterMap as $slug => $poster) {
            DB::table('events')->where('slug', $slug)->update(['poster' => $poster]);
        }
    }

    public function down(): void
    {
        Schema::table('registrations', function (Blueprint $table) {
            $columns = [
                'participant_name', 'participant_email', 'participant_phone',
                'participant_institution', 'participant_city', 'notes',
                'payment_method', 'payment_status', 'payment_amount',
                'payment_proof', 'paid_at', 'verified_at',
            ];
            $existing = array_filter($columns, fn (string $col) => Schema::hasColumn('registrations', $col));
            if ($existing !== []) {
                $table->dropColumn($existing);
            }
        });

        Schema::table('users', function (Blueprint $table) {
            $columns = ['institution', 'city'];
            $existing = array_filter($columns, fn (string $col) => Schema::hasColumn('users', $col));
            if ($existing !== []) {
                $table->dropColumn($existing);
            }
        });
    }
};
