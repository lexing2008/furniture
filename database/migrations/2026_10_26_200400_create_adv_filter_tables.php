<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('adv_filter_ints', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ad_id')->constrained()->cascadeOnDelete();
            $table->foreignId('filter_id')->constrained()->cascadeOnDelete();
            $table->integer('value');

            $table->unique(['ad_id', 'filter_id']);
        });

        Schema::create('adv_filter_floats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ad_id')->constrained()->cascadeOnDelete();
            $table->foreignId('filter_id')->constrained()->cascadeOnDelete();
            $table->double('value');

            $table->unique(['ad_id', 'filter_id']);
        });

        Schema::create('adv_filter_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ad_id')->constrained()->cascadeOnDelete();
            $table->foreignId('filter_id')->constrained()->cascadeOnDelete();
            $table->foreignId('filter_variant_id')->constrained()->cascadeOnDelete();

            $table->unique(['ad_id', 'filter_variant_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('adv_filter_variants');
        Schema::dropIfExists('adv_filter_floats');
        Schema::dropIfExists('adv_filter_ints');
    }
};
