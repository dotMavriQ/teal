<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Populates published_date from date_pub for books missing published_date.
     * This fixes the issue where Goodreads JSON import didn't properly populate
     * published_date but did have date_pub (the original publication year).
     */
    public function up(): void
    {
        // SQLite: Populate published_date from date_pub (which contains year-only dates like "2009", "1980", etc)
        // Only update if published_date is empty and date_pub looks like a year (1-4 digits)
        DB::update(
            'UPDATE books
             SET published_date = date_pub || \'-01-01\'
             WHERE (published_date IS NULL OR published_date = \'\')
             AND date_pub IS NOT NULL
             AND date_pub != \'\'
             AND LENGTH(date_pub) <= 4
             AND CAST(date_pub AS INTEGER) > 0'
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // This is not reversible since we can't reliably detect which rows were auto-populated
        // So we won't implement a down migration - treat this as a one-way data correction
    }
};
