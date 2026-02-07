<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\JsonImportService;
use App\Models\User;
use Illuminate\Console\Command;

class ImportGoodreads extends Command
{
    protected $signature = 'import:goodreads {user_id : The user ID to import books for}';

    protected $description = 'Import books from goodreads.txt JSON file';

    public function handle(): int
    {
        $userId = (int) $this->argument('user_id');
        $user = User::find($userId);

        if (!$user) {
            $this->error("User with ID {$userId} not found");
            return 1;
        }

        $filePath = base_path('goodreads.txt');

        if (!file_exists($filePath)) {
            $this->error("File not found: {$filePath}");
            return 1;
        }

        $this->info('Reading goodreads.txt...');
        $content = file_get_contents($filePath);

        try {
            $service = new JsonImportService();
            $books = $service->parseJson($content);

            $this->info("Parsed {$books->count()} books");

            $updated = 0;
            $skipped = 0;

            foreach ($books as $bookData) {
                // Try to find existing book by title and author
                $existing = $user->books()
                    ->where('title', $bookData['title'])
                    ->where('author', $bookData['author'])
                    ->first();

                if ($existing) {
                    // Only update page_count field
                    if ($bookData['page_count'] !== null && $bookData['page_count'] !== $existing->page_count) {
                        $existing->update(['page_count' => $bookData['page_count']]);
                        $updated++;
                        $this->line("  ✓ {$existing->title}: {$existing->page_count} pages");
                    } else {
                        $skipped++;
                    }
                } else {
                    $skipped++;
                }
            }

            $this->info("✓ Updated page counts: {$updated} books");
            $this->info("✓ No changes needed: {$skipped} books");

            return 0;
        } catch (\Exception $e) {
            $this->error('Import failed: ' . $e->getMessage());
            return 1;
        }
    }
}
