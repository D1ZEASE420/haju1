<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('my_favorite_subject', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');

            // Required base fields
            $table->string('title');
            $table->string('image')->nullable();
            $table->text('description');

            // Topic-specific extra fields
            $table->string('artist');
            $table->unsignedSmallInteger('release_year');
            $table->string('genre');
            $table->unsignedTinyInteger('rating')->default(3); // 1–5

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('my_favorite_subject');
    }
};