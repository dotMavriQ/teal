<?php
/**
 * TEAL Data Migration: SQLite → PostgreSQL
 * Run inside the app container: php /tmp/migrate-sqlite-data.php
 */

// Bootstrap Laravel
require '/var/www/html/vendor/autoload.php';
$app = require_once '/var/www/html/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// Configure SQLite backup connection
config(['database.connections.sqlite_backup' => [
    'driver' => 'sqlite',
    'database' => '/tmp/teal-backup.sqlite',
]]);

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
];

// Date columns that might have malformed values (2-digit years, etc.)
$dateColumns = [
    'books' => ['date_pub', 'date_pub_edition', 'date_started', 'date_added', 'date_recorded', 'created_at', 'updated_at'],
    'movies' => ['date_watched', 'created_at', 'updated_at'],
    'anime' => ['date_started', 'date_finished', 'created_at', 'updated_at'],
    'shows' => ['created_at', 'updated_at'],
    'episodes' => ['date_watched', 'created_at', 'updated_at'],
    'users' => ['created_at', 'updated_at', 'email_verified_at'],
    'shelves' => ['created_at', 'updated_at'],
    'comics' => ['created_at', 'updated_at'],
    'comic_issues' => ['created_at', 'updated_at'],
];

function fixDate($value) {
    if (empty($value) || $value === '0' || $value === '0000-00-00') {
        return null;
    }
    // Fix 2-digit year dates like "98-01-01" → null (can't reliably fix)
    if (preg_match('/^\d{2}-\d{2}-\d{2}$/', $value)) {
        return null;
    }
    // Fix dates that are just years like "2005"
    if (preg_match('/^\d{4}$/', $value)) {
        return $value . '-01-01';
    }
    return $value;
}

echo "=== TEAL SQLite → PostgreSQL Data Migration ===\n\n";

foreach ($tables as $table) {
    $rows = DB::connection('sqlite_backup')->table($table)->get();
    $count = $rows->count();

    if ($count === 0) {
        echo "SKIP: $table (empty)\n";
        continue;
    }

    // Truncate and reset sequence
    DB::statement("TRUNCATE TABLE \"$table\" CASCADE");

    $fixableCols = $dateColumns[$table] ?? [];
    $inserted = 0;
    $errors = 0;

    foreach ($rows->chunk(50) as $chunk) {
        $data = $chunk->map(function ($row) use ($fixableCols, $table) {
            $arr = (array) $row;
            // Fix date columns
            foreach ($fixableCols as $col) {
                if (isset($arr[$col])) {
                    $arr[$col] = fixDate($arr[$col]);
                }
            }
            // Convert empty strings to null for nullable columns
            foreach ($arr as $key => $val) {
                if ($val === '') {
                    $arr[$key] = null;
                }
            }
            return $arr;
        })->toArray();

        try {
            DB::table($table)->insert($data);
            $inserted += count($data);
        } catch (\Exception $e) {
            // Try row-by-row to isolate failures
            foreach ($data as $row) {
                try {
                    DB::table($table)->insert($row);
                    $inserted++;
                } catch (\Exception $e2) {
                    $errors++;
                    $id = $row['id'] ?? '?';
                    echo "  ERR: $table id=$id: " . substr($e2->getMessage(), 0, 120) . "\n";
                }
            }
        }
    }

    // Reset auto-increment sequence
    $maxId = DB::table($table)->max('id');
    if ($maxId) {
        DB::statement("SELECT setval(pg_get_serial_sequence('\"$table\"', 'id'), $maxId)");
    }

    echo "OK: $table ($inserted inserted" . ($errors ? ", $errors errors" : "") . ")\n";
}

echo "\n=== Migration complete ===\n";

// Verify counts
echo "\nVerification:\n";
foreach ($tables as $table) {
    $srcCount = DB::connection('sqlite_backup')->table($table)->count();
    $dstCount = DB::table($table)->count();
    $status = $srcCount === $dstCount ? 'MATCH' : 'MISMATCH';
    echo "  $table: SQLite=$srcCount PostgreSQL=$dstCount [$status]\n";
}
