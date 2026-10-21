<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained()->restrictOnDelete();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->string('title', 160);
            $table->string('slug', 190)->unique();
            $table->text('description');
            $table->string('poster')->nullable();
            $table->string('organizer', 120);
            $table->string('location', 160);
            $table->string('city', 80)->index();
            $table->dateTime('starts_at')->index();
            $table->dateTime('ends_at');
            $table->unsignedInteger('quota');
            $table->unsignedInteger('price')->default(0); // dalam Rupiah, 0 = gratis
            $table->string('status', 20)->default('draft'); // draft, published, closed, cancelled
            $table->boolean('is_featured')->default(false);
            $table->timestamps();

            $table->index(['status', 'starts_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
