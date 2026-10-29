<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ad_images', function (Blueprint $table) {
            $table->unsignedInteger('width')->nullable();
            $table->unsignedInteger('height')->nullable();
            $table->unsignedInteger('thumb_100_width')->nullable();
            $table->unsignedInteger('thumb_100_height')->nullable();
            $table->unsignedInteger('thumb_325_width')->nullable();
            $table->unsignedInteger('thumb_325_height')->nullable();
            $table->unsignedInteger('thumb_630_width')->nullable();
            $table->unsignedInteger('thumb_630_height')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('ad_images', function (Blueprint $table) {
            $table->dropColumn([
                'width',
                'height',
                'thumb_100_width',
                'thumb_100_height',
                'thumb_325_width',
                'thumb_325_height',
                'thumb_630_width',
                'thumb_630_height',
            ]);
        });
    }
};
