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
        Schema::table('world_heritage_sites', function (Blueprint $table) {
                $table->dropColumn('primary_image_url');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('world_heritage_sites', function (Blueprint $table) {
            $table->string('primary_image_url')->nullable()->after('short_description');
        });
    }
};
