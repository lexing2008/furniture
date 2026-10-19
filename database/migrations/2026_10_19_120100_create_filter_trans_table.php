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
        Schema::create('filter_trans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('filter_id')->constrained()->cascadeOnDelete();
            $table->string('lang', 5);
            $table->foreign('lang')
                ->references('code')
                ->on('languages')
                ->cascadeOnUpdate()
                ->restrictOnDelete();
            $table->string('name');
            // Unit of measurement: "см", "кг"
            $table->string('unit', 50)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['filter_id', 'lang']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('filter_trans');
    }
};
