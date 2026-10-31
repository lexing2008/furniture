<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('regions', function (Blueprint $table) {
            // The country a region belongs to: itself for a country, the root country for an area or city.
            $table->foreignId('country_id')
                ->nullable()
                ->after('parent_id')
                ->constrained('regions')
                ->nullOnDelete();
        });

        $regions = DB::table('regions')->get(['id', 'parent_id']);
        $parents = $regions->pluck('parent_id', 'id')->all();
        $roots = [];

        foreach ($regions as $region) {
            $id = (int) $region->id;
            $path = [];
            $current = $id;

            while (isset($parents[$current]) && $parents[$current] !== null) {
                $path[] = $current;
                $current = (int) $parents[$current];
            }

            foreach ([$current, ...$path] as $regionId) {
                $roots[$regionId] = $current;
            }
        }

        foreach ($roots as $regionId => $countryId) {
            DB::table('regions')->where('id', $regionId)->update(['country_id' => $countryId]);
        }
    }

    public function down(): void
    {
        Schema::table('regions', function (Blueprint $table) {
            $table->dropConstrainedForeignId('country_id');
        });
    }
};
