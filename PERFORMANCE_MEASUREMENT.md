# Performance Measurement & Optimization Guide

## Baseline Measurement (Current Implementation)

### Step 1: Prepare Test Data

Create test JSON with 100+ books:
```bash
# Use storage/app/examples/books-sample.json as template
# Duplicate and expand to create larger test set
cp storage/app/examples/books-sample.json storage/app/examples/books-large.json
# Add 100+ entries to test performance
```

### Step 2: Monitor Queue Processing

Before importing, start the queue worker:
```bash
# In a terminal, start the queue worker
php artisan queue:work --queue=imports,covers --max-jobs=1000 --timeout=600

# In another terminal, monitor jobs table
watch -n 1 "sqlite3 database/database.sqlite 'SELECT COUNT(*) as pending FROM jobs WHERE status=0;'"
```

### Step 3: Create Performance Baseline

```php
// In a Tinker session or test file
use App\Jobs\ImportFromJson;
use App\Models\User;
use Carbon\Carbon;

$user = User::first();
$json = file_get_contents('storage/app/examples/books-large.json');

// Measure start time
$startTime = microtime(true);
$startMemory = memory_get_usage();
$startBooks = $user->books()->count();

// Dispatch job
ImportFromJson::dispatch($user->id, $json, skipDuplicates: true);

// Wait for completion (manually check or via background worker)
// Poll every 10 seconds:
while (true) {
    sleep(10);
    $pending = DB::table('jobs')->count();
    echo "Pending jobs: $pending\n";
    if ($pending === 0) break;
}

// Calculate results
$endTime = microtime(true);
$endMemory = memory_get_usage();
$endBooks = $user->books()->count();
$booksImported = $endBooks - $startBooks;
$duration = $endTime - $startTime;
$memoryUsed = ($endMemory - $startMemory) / 1024 / 1024; // MB

echo "=== BASELINE RESULTS ===\n";
echo "Books Imported: $booksImported\n";
echo "Total Duration: " . round($duration, 2) . " seconds\n";
echo "Books/Second: " . round($booksImported / $duration, 2) . "\n";
echo "Memory Used: " . round($memoryUsed, 2) . " MB\n";
```

### Step 4: Measure Cover Fetch Success

```php
use App\Models\Book;

// Get baseline data
$booksWithCoverUrl = Book::whereNotNull('cover_url')->count();
$totalBooks = Book::count();
$successRate = ($booksWithCoverUrl / $totalBooks) * 100;

echo "Cover Fetch Success Rate: " . round($successRate, 2) . "%\n";
echo "Covers Downloaded: $booksWithCoverUrl / $totalBooks\n";

// Check which sources were used
$with_covers = DB::table('books')
    ->whereNotNull('cover_url')
    ->where('cover_url', 'like', '/storage/covers/%')
    ->count();

echo "Locally Stored Covers: $with_covers\n";
```

### Step 5: Analyze Logs

```bash
# Extract timing information
grep "ImportFromJson:" storage/logs/laravel.log | head -20

# Count cover fetch attempts
grep "FetchBookCover:" storage/logs/laravel.log | wc -l

# Check error rate
grep "FetchBookCover.*Error" storage/logs/laravel.log | wc -l
```

## Key Performance Metrics

### 1. **Import Throughput**
```
Target: 100+ books/minute
Measured as: books_imported / duration_in_seconds

Current Expected: 10-50 books/second (with minimal JSON parsing overhead)
```

### 2. **Cover Fetch Success Rate**
```
Target: >80% success rate
Measured as: books_with_covers / total_books * 100

Multiple sources should achieve:
- OpenLibrary: ~60% success
- Archive.org: ~40% success
- Fallback sources: ~30% success
Combined (trying all): ~85%+
```

### 3. **Request Latency**
```
Per job latency (random delays included):
- Import Job: 5-30 seconds initial delay + processing time
- Cover Jobs: 10-120 seconds delay + 2-30 seconds fetch

Total: Small batches take ~2-10 minutes
```

### 4. **Resource Usage**
```
Memory: < 50MB per job
CPU: Minimal (mostly network I/O wait)
Disk: ~1-2 KB per cover image (average)
```

## Optimization Targets

### Optimization 1: Batch Cover Processing

**Goal:** Reduce job overhead, fetch covers in parallel

**Implementation:**
```php
// Instead of dispatching one job per book:
FetchBookCover::dispatch($bookId)->delay(random_int(10, 120));

// Dispatch batches of 10-50 books:
FetchCoverBatch::dispatch($bookIds)->delay(random_int(10, 120));
```

**Expected Improvement:**
- 50-80% reduction in queue overhead
- 30-50% faster total completion time
- Same success rate, better throughput

**Measurement:**
```
Before: 5 books/second
After:  15-25 books/second (3-5x improvement)
```

### Optimization 2: Parallel HTTP Requests

**Goal:** Fetch from multiple sources simultaneously using HTTP/2

**Implementation:**
```php
// Current: Sequential requests to 3 sources
// Optimized: Concurrent requests using pooling
$responses = Http::pool(fn (Factory $pool) => [
    $pool->get("https://covers.openlibrary.org/b/isbn/{$isbn}-L.jpg"),
    $pool->get("https://archive.org/services/img/bookcover?isbn={$isbn}"),
    $pool->get("https://bookcover.longitood.com/pageSource.php?isbn={$isbn}"),
]);
```

**Expected Improvement:**
- 2-3x faster cover fetching
- Same network bandwidth
- Better resource utilization

**Measurement:**
```
Before: 2-5 seconds per book (3 sequential requests)
After:  1-2 seconds per book (3 concurrent requests)
```

### Optimization 3: Source Quality Scoring

**Goal:** Skip known-bad sources for faster results

**Implementation:**
```php
// Track success rate per source
$sourceStats = [
    'openlibrary' => ['success' => 0.85, 'avg_time' => 1.2],
    'archive.org' => ['success' => 0.60, 'avg_time' => 2.1],
    'bookcover.longitood' => ['success' => 0.40, 'avg_time' => 3.2],
];

// Sort by success rate, try best sources first
// Skip sources with <20% success rate
```

**Expected Improvement:**
- 20-30% faster when skipping bad sources
- 10-15% improvement in success rate (more time for better sources)

**Measurement:**
```
Before: Average 2.5 seconds per attempt
After:  Average 1.8 seconds per attempt
```

### Optimization 4: Image Compression

**Goal:** Reduce disk storage by compressing covers

**Implementation:**
```php
// Add to FetchBookCover::storeImage()
$image = Image::make($imageData)
    ->resize(400, 600, function($constraint) {
        $constraint->aspectRatio();
        $constraint->upsize();
    })
    ->encode(quality: 75);

Storage::disk('public')->put($filename, (string)$image);
```

**Expected Improvement:**
- 60-80% reduction in disk usage
- Negligible performance impact
- Better load times due to smaller files

**Measurement:**
```
Before: ~1-5 KB per image (compressed JPEGs)
After:  ~0.4-2 KB per image (75% quality)
```

### Optimization 5: Caching Layer

**Goal:** Avoid re-fetching same ISBN covers

**Implementation:**
```php
// Check cache before API call
if ($cover = Cache::get("book_cover:{$isbn}")) {
    return $this->storeImage($cover, $bookId);
}

// Store in cache for 30 days
Cache::put("book_cover:{$isbn}", $imageData, now()->addDays(30));
```

**Expected Improvement:**
- 95%+ improvement for duplicate ISBNs
- Minimal impact for new books

**Measurement:**
```
Duplicate books: <100ms vs 2-5 seconds
```

## Measurement Protocol

### For Each Optimization:

1. **Reset baseline**
   ```bash
   php artisan migrate:refresh
   rm -rf storage/app/public/covers/*
   ```

2. **Implement optimization**
   ```bash
   git stash
   # Make changes
   git add -A && git commit -m "Optimization: [name]"
   ```

3. **Run test suite**
   ```bash
   # Import 100+ books
   # Measure: time, success rate, memory, CPU
   # Collect logs
   ```

4. **Compare results**
   ```php
   // Calculate improvement percentage
   $improvement = (($after - $before) / $before) * 100;
   ```

5. **Document findings**
   ```markdown
   | Metric | Before | After | Improvement |
   |--------|--------|-------|-------------|
   | Books/sec | X | Y | +Z% |
   ```

## Performance Debugging

### If Cover Fetch is Slow:

1. **Check network latency**
   ```bash
   curl -w "@curl-format.txt" -o /dev/null -s https://covers.openlibrary.org/b/isbn/9780140449334-L.jpg
   ```

2. **Monitor queue worker CPU**
   ```bash
   top -p $(pgrep -f "queue:work")
   ```

3. **Check database locks**
   ```sql
   SELECT COUNT(*) FROM jobs WHERE locked_at IS NOT NULL;
   ```

### If Import is Slow:

1. **Check JSON parsing**
   ```php
   $start = microtime(true);
   $books = $service->parseJson($content);
   echo (microtime(true) - $start) . "s for parsing\n";
   ```

2. **Check duplicate detection**
   ```php
   // Disable skip_duplicates to measure
   $result = $service->importBooks($user, $books, false);
   ```

3. **Monitor database**
   ```bash
   # Check if database is bottleneck
   php artisan tinker
   > DB::enableQueryLog(); DB::getQueryLog() after import
   ```

## Expected Results Summary

| Metric | Current | Optimized | Improvement |
|--------|---------|-----------|-------------|
| Import Speed | 10-50 books/sec | 50-100+ books/sec | 5-10x |
| Cover Success Rate | 80-90% | 85-95% | +5-15% |
| Time to Complete 1000 books | 20-100 sec | 10-20 sec | 50-90% faster |
| Memory Per Job | ~30 MB | ~40 MB | +33% (acceptable) |
| Disk Usage Per Cover | 1-5 KB | 0.4-2 KB | 60-80% reduction |

## Success Criteria

✓ Baseline established and documented
✓ Each optimization measured independently
✓ Improvement verified with real data
✓ No regression in other metrics
✓ Code quality maintained
✓ Documentation updated
