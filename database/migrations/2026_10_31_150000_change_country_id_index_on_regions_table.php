<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $indexes = collect(Schema::getIndexes('regions'))->pluck('name');

        if (! $indexes->contains('regions_country_id_is_capital_index')) {
            Schema::table('regions', function (Blueprint $table) {
                $table->index(['country_id', 'is_capital'], 'regions_country_id_is_capital_index');
            });
        }

        // MySQL creates an index for the foreign key automatically. SQLite does not.
        if (Schema::getConnection()->getDriverName() !== 'sqlite' && $indexes->contains('regions_country_id_foreign')) {
            Schema::table('regions', function (Blueprint $table) {
                $table->dropIndex('regions_country_id_foreign');
            });
        }
    }

    public function down(): void
    {
        $indexes = collect(Schema::getIndexes('regions'))->pluck('name');

        if (Schema::getConnection()->getDriverName() !== 'sqlite' && ! $indexes->contains('regions_country_id_index')) {
            Schema::table('regions', function (Blueprint $table) {
                $table->index('country_id', 'regions_country_id_index');
            });
        }

        if ($indexes->contains('regions_country_id_is_capital_index')) {
            Schema::table('regions', function (Blueprint $table) {
                $table->dropIndex('regions_country_id_is_capital_index');
            });
        }
    }
};
