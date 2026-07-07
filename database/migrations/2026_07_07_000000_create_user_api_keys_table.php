<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_api_keys', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            // The credential slug, e.g. "tmdb.access_token" (see config/api_keys.php).
            $table->string('service');
            // Encrypted at rest via Laravel's Crypt (the `encrypted` cast). A raw
            // database dump never exposes the plaintext key.
            $table->text('key');
            $table->timestamps();

            $table->unique(['user_id', 'service']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_api_keys');
    }
};
