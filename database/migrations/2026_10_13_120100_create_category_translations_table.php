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
        Schema::create('category_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            // Linked by languages.code (unique), so translations are readable without a join: "ru", "en"
            $table->string('lang', 5);
            $table->foreign('lang')
                ->references('code')
                ->on('languages')
                ->cascadeOnUpdate()
                ->restrictOnDelete();
            $table->string('name');
            $table->string('slug');
            $table->string('seo_title')->nullable();
            $table->string('seo_description', 500)->nullable();
            $table->text('seo_block')->nullable();
            $table->timestamps();

            $table->unique(['category_id', 'lang']);
            // Slug is unique within a language: /ru/divany and /en/sofas
            $table->unique(['lang', 'slug']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('category_translations');
    }
};
