<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_favorite', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id');
            $table->unsignedInteger('world_heritage_site_id');
            $table->timestamps();

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->cascadeOnDelete();

            $table->foreign('world_heritage_site_id')
                ->references('id')
                ->on('world_heritage_sites')
                ->cascadeOnDelete();

            $table->unique(['user_id', 'world_heritage_site_id'], 'user_favorite_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_favorite');
    }
};