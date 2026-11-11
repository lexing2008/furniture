<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('region_countries', function (Blueprint $table) {
            $table->string('tax_id_label', 50)->nullable()->after('currency_decimals');
        });
    }

    public function down(): void
    {
        Schema::table('region_countries', function (Blueprint $table) {
            $table->dropColumn('tax_id_label');
        });
    }
};
