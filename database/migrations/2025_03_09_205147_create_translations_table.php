<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTranslationsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('translations', function (Blueprint $table) {
            $table->id();
            $table->string('locale')->index(); // e.g., 'en', 'fr', 'es'
            $table->string('key')->index(); // Translation key, e.g., 'welcome_message'
            $table->text('value'); // Translation value
            $table->json('tags')->nullable(); // e.g., ["mobile", "web"]
            $table->timestamps();

            // Composite unique key to prevent duplicate keys per locale
            $table->unique(['locale', 'key']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('translations');
    }
}
