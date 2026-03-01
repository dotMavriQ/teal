<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Populates published_date from date_pub for books missing published_date.
     * This fixes the issue where JSON import didn't properly populate
     * published_date but did have date_pub (the original publication year).
     */
    public function up(): void
    {
        // Populate published_date from date_pub (which contains year-only dates like "2009", "1980", etc)
        // Only update if published_date is NULL and date_pub looks like a year (1-4 digits)
        // Explicitly target pgsql connection for Postgres-specific syntax
        if (config('database.default') === 'pgsql' || DB::connection()->getDriverName() === 'pgsql') {
            DB::connection('pgsql')->update(
                "UPDATE books
                 SET published_date = CAST(date_pub || '-01-01' AS DATE)
                 WHERE published_date IS NULL
                 AND date_pub IS NOT NULL
                 AND date_pub ~ '^[0-9]{1,4}$'"
            );
        }
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
