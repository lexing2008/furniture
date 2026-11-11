<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ads', function (Blueprint $table) {
            $table->unsignedSmallInteger('images_count')->default(0)->after('warranty_unit');
        });

        DB::table('ads')->update([
            'images_count' => DB::raw('(SELECT COUNT(*) FROM ad_images WHERE ad_images.ad_id = ads.id)'),
        ]);
    }

    public function down(): void
    {
        Schema::table('ads', function (Blueprint $table) {
            $table->dropColumn('images_count');
        });
    }
};
