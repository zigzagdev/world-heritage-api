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
        Schema::create('images', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('world_heritage_id')->index();
            $table->string('disk');
            $table->string('path');
            $table->unsignedInteger('width')->nullable();
            $table->unsignedInteger('height')->nullable();
            $table->string('format', 10)->nullable();
            $table->string('checksum', 64)->nullable()->index();
            $table->unsignedInteger('sort_order')->default(0);
            $table->string('alt')->nullable();
            $table->string('credit')->nullable();
            $table->timestamps();

            $table->foreign('world_heritage_id')
                ->references('id')
                ->on('world_heritage_sites')
                ->cascadeOnDelete();

            $table->unique(['disk', 'path']);
            $table->index(['world_heritage_id', 'sort_order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('images');
    }
};
