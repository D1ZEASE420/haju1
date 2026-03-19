<?php

// database/migrations/xxxx_create_markers_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('markers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->decimal('latitude', 10, 7);
            $table->decimal('longitude', 10, 7);
            $table->text('description')->nullable();
            $table->timestamps(); // this gives you 'created_at' (added) and 'updated_at' (edited)
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('markers');
    }
};