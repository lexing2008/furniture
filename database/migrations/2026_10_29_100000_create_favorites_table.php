<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('favorites', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ad_id')->constrained()->cascadeOnDelete();
            // The region in which the ad was added to favorites.
            $table->foreignId('region_id')->constrained()->restrictOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('visitor_token', 64)->nullable()->index();
            $table->timestamps();

            // One favorite per ad: the region is metadata "where it was added".
            $table->unique(['user_id', 'ad_id']);
            $table->unique(['visitor_token', 'ad_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('favorites');
    }
};
