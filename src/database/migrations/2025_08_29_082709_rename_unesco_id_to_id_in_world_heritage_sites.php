<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('site_state_parties', function (Blueprint $table) {
            $table->dropForeign(['world_heritage_site_unesco_id']);
        });

        Schema::table('world_heritage_sites', function (Blueprint $table) {
            $table->renameColumn('unesco_id', 'id');
        });

        DB::statement('ALTER TABLE world_heritage_sites DROP PRIMARY KEY');
        DB::statement('ALTER TABLE world_heritage_sites ADD PRIMARY KEY (id)');

        Schema::table('site_state_parties', function (Blueprint $table) {
            $table->renameColumn('world_heritage_site_unesco_id', 'world_heritage_site_id');
            $table->foreign('world_heritage_site_id')
                ->references('id')
                ->on('world_heritage_sites')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('site_state_parties', function (Blueprint $table) {
            $table->dropForeign(['world_heritage_site_id']);
        });

        Schema::table('world_heritage_sites', function (Blueprint $table) {
            $table->renameColumn('id', 'unesco_id');
        });

        DB::statement('ALTER TABLE world_heritage_sites DROP PRIMARY KEY');
        DB::statement('ALTER TABLE world_heritage_sites ADD PRIMARY KEY (unesco_id)');

        Schema::table('site_state_parties', function (Blueprint $table) {
            $table->renameColumn('world_heritage_site_id', 'world_heritage_site_unesco_id');

            $table->foreign('world_heritage_site_unesco_id')
                ->references('unesco_id')
                ->on('world_heritage_sites')
                ->cascadeOnDelete();
        });
    }
};
