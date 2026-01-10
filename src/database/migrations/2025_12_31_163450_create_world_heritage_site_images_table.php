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
        Schema::create('world_heritage_site_images', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('world_heritage_site_id');
            $table->text('url');
            $table->char('url_hash', 64);
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_primary')->default(false);
            $table->timestamps();

            $table->foreign('world_heritage_site_id')
                ->references('id')
                ->on('world_heritage_sites')
                ->cascadeOnDelete();

            $table->unique(['world_heritage_site_id', 'url_hash'], 'wh_site_images_unique');
            $table->index(['world_heritage_site_id', 'sort_order'], 'wh_site_images_site_sort_idx');
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('world_heritage_site_images');
    }
};
