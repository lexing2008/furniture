<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ad_images', function (Blueprint $table) {
            // Stable identifier used in the file names; the slug is only a prefix
            $table->uuid('uuid')->nullable()->after('path');
            $table->unique('uuid');
        });

        Schema::table('tmp_files', function (Blueprint $table) {
            $table->uuid('uuid')->nullable()->after('path');
            $table->unique('uuid');
        });
    }

    public function down(): void
    {
        Schema::table('tmp_files', function (Blueprint $table) {
            $table->dropUnique(['uuid']);
            $table->dropColumn('uuid');
        });

        Schema::table('ad_images', function (Blueprint $table) {
            $table->dropUnique(['uuid']);
            $table->dropColumn('uuid');
        });
    }
};
