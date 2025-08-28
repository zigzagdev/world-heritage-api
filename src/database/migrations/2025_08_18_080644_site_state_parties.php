<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('site_state_parties', function (Blueprint $table) {
            $table->char('state_party_code', 3);
            $table->unsignedInteger('world_heritage_site_unesco_id');

            $table->boolean('is_primary')->default(false);
            $table->unsignedSmallInteger('inscription_year')->nullable();
            $table->timestamps();

            $table->foreign('state_party_code')
                ->references('state_party_code')->on('countries')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->foreign('world_heritage_site_unesco_id')
                ->references('unesco_id')->on('world_heritage_sites')
                ->cascadeOnDelete();

            $table->primary(['state_party_code', 'world_heritage_site_unesco_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('site_state_parties');
    }
};
