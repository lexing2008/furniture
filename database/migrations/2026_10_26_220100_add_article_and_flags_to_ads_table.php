<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ads', function (Blueprint $table) {
            $table->string('article', 255)->nullable()->after('number');
            $table->boolean('is_active')->default(true)->after('is_moderated');
            $table->boolean('is_banned')->default(false)->after('is_active');
        });
    }

    public function down(): void
    {
        Schema::table('ads', function (Blueprint $table) {
            $table->dropColumn(['article', 'is_active', 'is_banned']);
        });
    }
};
