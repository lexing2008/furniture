<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->foreignId('country_id')
                ->nullable()
                ->after('region_id')
                ->constrained('regions')
                ->nullOnDelete();
        });

        // Existing companies: the country is the root region of the company region
        $regions = DB::table('regions')->get(['id', 'parent_id'])->keyBy('id');

        foreach (DB::table('companies')->get(['id', 'region_id']) as $company) {
            $region = $regions->get($company->region_id);
            $countryId = null;

            while ($region !== null) {
                $countryId = $region->id;
                $region = $regions->get($region->parent_id);
            }

            if ($countryId !== null) {
                DB::table('companies')->where('id', $company->id)->update(['country_id' => $countryId]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropConstrainedForeignId('country_id');
        });
    }
};
