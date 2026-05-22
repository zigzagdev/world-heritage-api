<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('site_state_parties', function (Blueprint $table) {
            $table->index('world_heritage_site_id', 'idx_ssp_world_heritage_site_id');
        });
    }

    public function down(): void
    {
        Schema::table('site_state_parties', function (Blueprint $table) {
            $table->dropIndex('idx_ssp_world_heritage_site_id');
        });
    }
};
