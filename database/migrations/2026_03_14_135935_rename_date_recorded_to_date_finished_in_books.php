<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Clear dates for books that aren't "read" -- these are just Goodreads shelf timestamps
        DB::table('books')
            ->whereNot('status', 'read')
            ->update(['date_recorded' => null]);

        Schema::table('books', function (Blueprint $table) {
            $table->renameColumn('date_recorded', 'date_finished');
        });
    }

    public function down(): void
    {
        Schema::table('books', function (Blueprint $table) {
            $table->renameColumn('date_finished', 'date_recorded');
        });
    }
};
