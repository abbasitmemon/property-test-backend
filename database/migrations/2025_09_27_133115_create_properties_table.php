<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
         Schema::create('properties', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description');
            $table->decimal('price_per_night', 8, 2);
            $table->string('location');
            $table->json('amenities');
            $table->json('images');
            $table->foreignId('created_by')
              ->nullable()
              ->constrained('users')
              ->nullOnDelete();
            $table->foreignId('updated_by')
              ->nullable()
              ->constrained('users')
              ->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('properties');
    }
};
