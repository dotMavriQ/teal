<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class MigrateSqliteToPgsql extends Command
{
    protected $signature = 'app:migrate-sqlite-to-pgsql';
    protected $description = 'Migrate data from SQLite to PostgreSQL';

    public function handle()
    {
        $this->info('Starting migration from SQLite to PostgreSQL...');

        config(['database.connections.sqlite.database' => database_path('database.sqlite')]);

        $tables = [
            'users',
            'books',
            'shelves',
            'book_shelf',
            'movies',
            'shows',
            'episodes',
            'anime',
            'comics',
            'comic_issues',
            'sessions',
            'cache',
            'cache_locks',
            'jobs',
            'job_batches',
            'failed_jobs',
        ];

        foreach ($tables as $table) {
            if (!Schema::connection('sqlite')->hasTable($table)) {
                $this->warn("Table {$table} does not exist in SQLite, skipping.");
                continue;
            }

            $this->info("Migrating table: {$table}");

            $data = DB::connection('sqlite')->table($table)->get();

            if ($data->isEmpty()) {
                $this->line("No data for {$table}.");
                continue;
            }

            // Clear existing data in PGSQL
            DB::connection('pgsql')->table($table)->truncate();

            foreach ($data->chunk(100) as $chunk) {
                $insertData = json_decode(json_encode($chunk), true);
                
                // Sanitize dates for Postgres (prevent ancient dates from breaking)
                foreach ($insertData as &$row) {
                    foreach ($row as $key => $value) {
                        if (is_string($value) && (str_ends_with($key, '_date') || str_starts_with($key, 'date_') || str_ends_with($key, '_at'))) {
                            if (!empty($value) && preg_match('/^\d{1,4}-\d{2}-\d{2}/', $value)) {
                                $year = (int) explode('-', $value)[0];
                                if ($year < 1000) {
                                    $row[$key] = '1000-01-01';
                                }
                            }
                        }
                    }
                }

                DB::connection('pgsql')->table($table)->insert($insertData);
            }

            $this->info("Migrated " . $data->count() . " rows for {$table}.");

            // Reset sequences for Postgres
            $this->resetSequence($table);
        }

        $this->info('Migration completed successfully!');
    }

    private function resetSequence($table)
    {
        // Tables without numeric 'id' primary key or sequence
        if ($table === 'sessions' || $table === 'cache' || $table === 'cache_locks' || $table === 'job_batches') {
            return;
        }

        $idColumn = 'id';

        try {
            $maxId = DB::connection('pgsql')->table($table)->max($idColumn) ?: 0;
            $nextId = $maxId + 1;
            $sequenceName = "{$table}_{$idColumn}_seq";
            
            // Check if sequence exists
            $exists = DB::connection('pgsql')->select("SELECT 1 FROM pg_class WHERE relname = ?", [$sequenceName]);
            
            if (!empty($exists)) {
                DB::connection('pgsql')->statement("ALTER SEQUENCE {$sequenceName} RESTART WITH {$nextId}");
                $this->line("Reset sequence {$sequenceName} to {$nextId}");
            }
        } catch (\Exception $e) {
            $this->warn("Could not reset sequence for {$table}: " . $e->getMessage());
        }
    }
}
