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
        Schema::create('world_heritage_sites', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('unesco_id')->unique();
            $table->string('official_name');
            $table->string('name');
            $table->string('name_jp')->nullable();
            $table->string('country');
            $table->string('region');
            $table->string('state_party', 5)->nullable();
            $table->enum('category', ['cultural', 'natural', 'mixed']);
            $table->json('criteria')->nullable();
            $table->year('year_inscribed');
            $table->float('area_hectares')->nullable();
            $table->float('buffer_zone_hectares')->nullable();
            $table->boolean('is_endangered')->default(false);
            $table->decimal('latitude', 10, 6)->nullable();
            $table->decimal('longitude', 10, 6)->nullable();
            $table->text('short_description')->nullable();
            $table->string('image_url')->nullable();
            $table->string('unesco_site_url')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('world_heritage_sites');
    }
};
