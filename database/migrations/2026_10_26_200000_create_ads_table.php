<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->constrained()->restrictOnDelete();
            $table->string('title');
            // The number in the company's own system
            $table->string('number', 255);
            $table->string('video', 2048)->nullable();
            $table->text('description')->nullable();
            $table->decimal('price', 15, 4);
            $table->string('currency', 3);
            $table->unsignedSmallInteger('warranty_value')->nullable();
            // days, months, years
            $table->string('warranty_unit', 10)->default('months');
            $table->timestamps();

            $table->unique(['company_id', 'number']);
            $table->index('category_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ads');
    }
};
