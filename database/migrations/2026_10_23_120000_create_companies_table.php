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
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            // The account of the company: one user, one company
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            // A city: the company is shown in this region
            $table->foreignId('region_id')->constrained()->restrictOnDelete();
            $table->string('legal_name');
            $table->string('tax_id', 50);
            $table->string('display_name');
            // HTML, cleaned on save (App\Casts\PurifiedHtml, profile "company")
            $table->text('description')->nullable();
            $table->boolean('is_online_store')->default(false);
            $table->string('website')->nullable();
            // Contact email shown to visitors; the login email is users.email
            $table->string('email')->nullable();
            // {"facebook": "https://...", "youtube": "https://..."}
            $table->json('socials')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('companies');
    }
};
