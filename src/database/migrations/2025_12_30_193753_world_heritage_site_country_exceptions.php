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
        Schema::create('world_heritage_site_country_exceptions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('world_heritage_site_id');
            $table->string('reason', 100);
            $table->json('raw')->nullable();
            $table->timestamps();

            $table->foreign('world_heritage_site_id', 'world_heritage_fk_site')
                ->references('id')
                ->on('world_heritage_sites')
                ->cascadeOnDelete();

            $table->unique(['world_heritage_site_id', 'reason'], 'world_heritage_uq_site_reason');
            $table->index('world_heritage_site_id', 'world_heritage_ix_site');
            $table->index('reason', 'world_heritage_ix_reason');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('world_heritage_site_country_exceptions');
    }
};
