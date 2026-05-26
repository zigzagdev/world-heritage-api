<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('world_heritage_sites', function (Blueprint $table) {
            $table->string('main_video_url')->nullable()->after('main_image_url');
        });
    }

    public function down(): void
    {
        Schema::table('world_heritage_sites', function (Blueprint $table) {
            $table->dropColumn('main_video_url');
        });
    }
};