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
        Schema::create('world_heritage_descriptions', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('world_heritage_site_id');
            $table->text('short_description_en');
            $table->text('short_description_ja')->nullable();
            $table->text('description_en');
            $table->text('description_ja')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->foreign('world_heritage_site_id')
                ->references('id')
                ->on('world_heritage_sites')
                ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('world_heritage_descriptions');
    }
};
