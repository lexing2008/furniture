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
        Schema::create('filter_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('filter_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('order')->default(0);
            // Default template for all languages of the variant; a translation can override it
            $table->foreignId('seo_template_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['filter_id', 'order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('filter_variants');
    }
};
