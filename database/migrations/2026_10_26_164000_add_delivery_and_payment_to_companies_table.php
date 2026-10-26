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
        Schema::table('companies', function (Blueprint $table) {
            // HTML, cleaned on save (App\Casts\PurifiedHtml, profile "company")
            $table->text('delivery')->nullable()->after('description');
            $table->text('payment')->nullable()->after('delivery');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn(['delivery', 'payment']);
        });
    }
};
