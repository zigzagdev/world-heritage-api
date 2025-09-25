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
            $table->unsignedInteger('id')->primary();
            $table->string('official_name');
            $table->string('name');
            $table->string('name_jp')->nullable();
            $table->string('country');
            $table->string('region');
            $table->char('state_party', 3)->nullable();

            $table->enum('category', ['Cultural','Natural','Mixed']);
            $table->json('criteria');

            $table->unsignedSmallInteger('year_inscribed');
            $table->decimal('area_hectares', 12, 2)->nullable();
            $table->decimal('buffer_zone_hectares', 12, 2)->nullable();

            $table->boolean('is_endangered')->default(false);

            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 11, 7)->nullable();

            $table->text('short_description')->nullable();
            $table->string('image_url')->nullable();
            $table->string('unesco_site_url');
            $table->timestamps();

            $table->index('state_party');
            $table->index('category');
            $table->index('year_inscribed');
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
