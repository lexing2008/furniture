<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ad_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ad_id')->constrained()->cascadeOnDelete();
            // Path relative to the public disk: furniture/manual/....
            $table->string('path');
            $table->unsignedInteger('order')->default(0);
            $table->timestamps();

            $table->index(['ad_id', 'order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ad_images');
    }
};
