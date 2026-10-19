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
        Schema::create('filter_variant_trans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('filter_variant_id')->constrained()->cascadeOnDelete();
            $table->string('lang', 5);
            $table->foreign('lang')
                ->references('code')
                ->on('languages')
                ->cascadeOnUpdate()
                ->restrictOnDelete();
            // "Угловой"
            $table->string('name_in_filter');
            // "Угловой диван"
            $table->string('name_full');
            // "Купить угловой диван"
            $table->string('name_buy');
            // Unique within a category and a language: checked in the application,
            // the category is reached only through the filter
            $table->string('slug');
            $table->foreignId('seo_template_id')->nullable()->constrained()->nullOnDelete();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['filter_variant_id', 'lang']);
            $table->index(['lang', 'slug']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('filter_variant_trans');
    }
};
