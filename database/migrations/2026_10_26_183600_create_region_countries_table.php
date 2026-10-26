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
        Schema::create('region_countries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('region_id')->unique()->constrained()->cascadeOnDelete();
            // Phone code without "+", for example "375"
            $table->string('phone_code', 10)->nullable();
            // ISO 4217 currency code, for example "BYN"
            $table->string('currency', 3)->nullable();
            // Number of digits after the decimal separator
            $table->unsignedTinyInteger('currency_decimals')->default(2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('region_countries');
    }
};
