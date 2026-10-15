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
        Schema::create('regions', function (Blueprint $table) {
            $table->id();
            // Country (null) -> area -> city. A parent with children cannot be removed by accident
            $table->foreignId('parent_id')->nullable()->constrained('regions')->restrictOnDelete();
            $table->string('name');
            $table->string('name_en');
            // Locative form: "в Берлине"
            $table->string('name_in');
            $table->string('subdomain', 63)->unique();
            // Only countries have a language, areas and cities use the language of their country
            $table->string('lang', 5)->nullable();
            $table->foreign('lang')
                ->references('code')
                ->on('languages')
                ->cascadeOnUpdate()
                ->restrictOnDelete();
            // Empty values of areas and cities are taken from their country
            $table->string('seo_title')->nullable();
            $table->string('seo_description', 500)->nullable();
            $table->text('seo_block')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('regions');
    }
};
