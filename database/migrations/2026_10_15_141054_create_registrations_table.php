<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('registrations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('event_id')->constrained()->cascadeOnDelete();
            $table->string('code', 20)->unique();
            $table->string('status', 20)->default('confirmed'); // confirmed, attended, cancelled
            $table->timestamp('registered_at');
            $table->timestamps();

            // Satu pengguna hanya boleh punya satu pendaftaran per event.
            $table->unique(['user_id', 'event_id']);
            $table->index(['event_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('registrations');
    }
};
