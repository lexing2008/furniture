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
        Schema::create('company_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            // 1 = Monday, ..., 7 = Sunday (ISO-8601)
            $table->unsignedTinyInteger('day_of_week');
            // Closed days keep 00:00 in both columns, "is_closed" is the source of truth
            $table->time('open_at');
            $table->time('close_at');
            $table->boolean('has_break')->default(false);
            $table->time('break_start_at')->nullable();
            $table->time('break_end_at')->nullable();
            $table->boolean('is_closed')->default(false);
            $table->timestamps();

            $table->index('day_of_week');
            $table->unique(['company_id', 'day_of_week']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('company_schedules');
    }
};
