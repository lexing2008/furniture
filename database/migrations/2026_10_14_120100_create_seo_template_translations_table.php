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
        Schema::create('seo_template_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('seo_template_id')->constrained()->cascadeOnDelete();
            $table->string('lang', 5);
            $table->foreign('lang')
                ->references('code')
                ->on('languages')
                ->cascadeOnUpdate()
                ->restrictOnDelete();
            $table->string('name');
            $table->string('seo_title')->nullable();
            $table->string('seo_description', 500)->nullable();
            $table->text('seo_block')->nullable();
            $table->timestamps();

            $table->unique(['seo_template_id', 'lang']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('seo_template_translations');
    }
};
