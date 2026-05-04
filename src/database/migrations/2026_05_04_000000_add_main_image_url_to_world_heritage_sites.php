<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('world_heritage_sites', function (Blueprint $table) {
            $table->string('main_image_url', 2048)->nullable()->after('short_description');
        });
    }

    public function down(): void
    {
        Schema::table('world_heritage_sites', function (Blueprint $table) {
            $table->dropColumn('main_image_url');
        });
    }
};