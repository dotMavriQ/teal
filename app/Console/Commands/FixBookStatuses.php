<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Enums\ReadingStatus;
use App\Models\Book;
use App\Models\Shelf;
use Illuminate\Console\Command;

class FixBookStatuses extends Command
{
    protected $signature = 'books:fix-statuses {--shelves : Also extract and create shelves}';

    protected $description = 'Fix book statuses and optionally extract shelves from the shelves field';

    private array $statusKeywords = ['read', 'to-read', 'currently-reading', 'want-to-read', 'reading'];

    public function handle(): int
    {
        $extractShelves = $this->option('shelves');
        $statusUpdated = 0;
        $shelvesCreated = 0;
        $booksWithShelves = 0;

        $total = Book::whereNotNull('shelves')->count();
        $this->info("Processing {$total} books with shelf data...");

        Book::whereNotNull('shelves')->with('user')->each(function (Book $book) use ($extractShelves, &$statusUpdated, &$shelvesCreated, &$booksWithShelves) {
            $shelfParts = array_map('trim', explode(',', $book->shelves ?? ''));

            // First part is always status
            $statusPart = strtolower($shelfParts[0] ?? '');

            // Determine correct status
            if (str_contains($statusPart, 'to-read') || str_contains($statusPart, 'want')) {
                $newStatus = ReadingStatus::WantToRead;
            } elseif (str_contains($statusPart, 'currently') || $statusPart === 'reading') {
                $newStatus = ReadingStatus::Reading;
            } elseif (str_contains($statusPart, 'read')) {
                $newStatus = ReadingStatus::Read;
            } else {
                $newStatus = ReadingStatus::WantToRead;
            }

            if ($book->status !== $newStatus) {
                $book->update(['status' => $newStatus]);
                $statusUpdated++;
            }

            // Extract custom shelves (everything after the first part that isn't a status keyword)
            if ($extractShelves && count($shelfParts) > 1) {
                $customShelves = [];

                for ($i = 1; $i < count($shelfParts); $i++) {
                    $shelfName = trim($shelfParts[$i]);
                    $shelfLower = strtolower($shelfName);

                    // Skip if it's a status keyword
                    if (empty($shelfName) || in_array($shelfLower, $this->statusKeywords)) {
                        continue;
                    }

                    $shelf = Shelf::findOrCreateForUser($book->user_id, $shelfName);
                    $customShelves[] = $shelf->id;
                    $shelvesCreated++;
                }

                if (! empty($customShelves)) {
                    $book->bookShelves()->syncWithoutDetaching($customShelves);
                    $booksWithShelves++;
                }
            }
        });

        $this->info("Updated {$statusUpdated} book statuses.");

        if ($extractShelves) {
            $this->info("Created/assigned {$shelvesCreated} shelf associations for {$booksWithShelves} books.");
        }

        return Command::SUCCESS;
    }
}
