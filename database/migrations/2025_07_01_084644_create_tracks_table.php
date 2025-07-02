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
        Schema::create('tracks', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('artist')->nullable();
            $table->string('album')->nullable();
            $table->string('genre')->nullable();
            $table->integer('year')->nullable();
            $table->integer('duration')->nullable(); // Duration in seconds
            $table->string('file_path'); // Path in Bunny.net storage
            $table->string('file_name');
            $table->string('file_extension');
            $table->bigInteger('file_size')->nullable(); // File size in bytes
            $table->string('cdn_url');
            $table->string('streaming_url');
            $table->text('metadata')->nullable(); // JSON field for additional metadata
            $table->timestamp('last_synced_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            // Indexes for better performance
            $table->index(['title', 'artist']);
            $table->index('genre');
            $table->index('is_active');
            $table->unique('file_path');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tracks');
    }
};
