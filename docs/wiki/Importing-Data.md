# Importing Data

TEAL supports importing your existing media libraries from several sources. All imports are non-destructive: if a record already exists, TEAL will skip it or fill in empty fields without overwriting what you already have.

## Table of Contents

- [IMDb Export (Movies & TV)](#imdb-export)
- [GoodReads Export (Books)](#goodreads-export)
- [JSON Import (Books)](#json-import)
- [MAL Import (Anime)](#mal-import)
- [ComicVine Search (Comics)](#comicvine-search)

---

## IMDb Export

**Route:** `/movies/import`
**Service:** `App\Services\ImdbImportService`
**Format:** CSV (exported from IMDb)

### How to Export from IMDb

Go to your IMDb ratings page and click "Export". This gives you a CSV file with all your rated titles.

### What Gets Imported

IMDb exports contain movies, TV shows, TV episodes, and more. TEAL routes each entry by its `Title Type` column:

| IMDb Title Type | TEAL Destination |
|----------------|-----------------|
| `Movie`, `TV Movie`, `Video`, `Short`, `TV Short`, `TV Special` | Movies table |
| `TV Series`, `TV Mini Series` | Shows table |
| `TV Episode` | Movies table (as episode, linked to parent show) |
| `Podcast Series` | Movies table |

### Field Mapping

| IMDb Column | TEAL Field |
|-------------|-----------|
| Const | `imdb_id` |
| Title | `title` |
| Original Title | `original_title` |
| Title Type | `title_type` |
| Directors | `director` |
| Year | `year` |
| Runtime (mins) | `runtime_minutes` |
| Genres | `genres` |
| Your Rating | `rating` (1-10) |
| IMDb Rating | `imdb_rating` |
| Num Votes | `num_votes` |
| Release Date | `release_date` |
| Date Rated | `date_watched` |
| URL | `imdb_url` |

### Episode Parsing

When TEAL encounters a TV Episode, it tries to extract the show name, season, and episode number from the title. It recognizes these patterns:

- `Show Name: Episode Title` (colon-separated)
- `Show Name: Episode #1.6` (season.episode)
- `Show Name S01E06` (standard notation)

If a parent Show record does not exist yet, TEAL creates one automatically.

### Duplicate Detection

Duplicates are detected by `imdb_id`. If a match is found, TEAL updates only the fields that are currently empty. Your manually entered data is never overwritten.

### Status Assignment

- If you have a rating, the entry is marked as **Watched**.
- If you do not have a rating, it is marked as **Watchlist**.

---

## GoodReads Export

**Route:** `/books/import`
**Service:** `App\Services\GoodReadsImportService`
**Format:** CSV (exported from GoodReads)

### How to Export from GoodReads

Go to My Books > Import and Export > Export Library. This gives you a CSV with your entire GoodReads library.

### Field Mapping

| GoodReads Column | TEAL Field |
|-----------------|-----------|
| Book Id | `goodreads_id` |
| Title | `title` |
| Author | `author` |
| Additional Authors | Appended to `author` |
| ISBN | `isbn` (digits only) |
| ISBN13 | `isbn13` (digits only) |
| Number of Pages | `page_count` |
| Publisher | `publisher` |
| Date Published | `published_date` |
| Exclusive Shelf | `status` |
| My Rating | `rating` (1-5 stars) |
| Date Started | `date_started` |
| Date Read | `date_recorded` |
| My Review | `notes` |

### ISBN Cleaning

GoodReads CSVs sometimes contain Excel artifacts in ISBN fields (leading equals signs, quotes). TEAL strips these automatically before storing.

### Status Mapping

| GoodReads Shelf | TEAL Status |
|----------------|------------|
| `currently-reading` | Currently Reading |
| `read` | Read |
| Everything else | Want to Read |

### Duplicate Detection

Checked in this order:

1. `goodreads_id`
2. `isbn13`
3. `isbn`

If any of these match an existing book, the import skips that row.

---

## JSON Import

**Route:** `/books/import`
**Service:** `App\Services\JsonImportService`
**Format:** JSON

This is a flexible JSON import that handles various export tool formats. It also supports custom shelf data, which gets turned into TEAL Shelf records.

### Field Mapping

| JSON Field | TEAL Field |
|-----------|-----------|
| `title` | `title` |
| `author` | `author` |
| `isbn`, `isbn13`, `asin` | `isbn`, `isbn13`, `asin` |
| `bookCover` | `cover_url` |
| `num_pages` | `page_count` |
| `date_pub` or `date_pub__ed__` | `published_date` |
| `rating` | `rating` (1-5) |
| `avg_rating` | `avg_rating` |
| `date_started` | `date_started` |
| `date_read` | `date_recorded` |
| `shelves` | `status` + custom shelves |
| `notes`, `review`, `comments` | `notes`, `review`, `comments` |

### Status Mapping

The `shelves` field is a comma-separated string. TEAL checks the shelf names in order:

1. Contains "to-read" or "want" -> **Want to Read**
2. Contains "currently" or "reading" -> **Currently Reading**
3. Contains "read" -> **Read**
4. Default -> **Want to Read**

Any shelf names that are not status keywords get created as custom Shelf records and linked to the book.

### Duplicate Detection

Checked in this order:

1. `isbn13`
2. `isbn`
3. `asin`
4. `title` + `author` combination

---

## MAL Import

**Route:** `/anime/import`
**Service:** `App\Services\MalImportService`
**Format:** JSON (via username) or XML (file upload)

### Option 1: Username Fetch

Enter your MyAnimeList username. TEAL fetches your list from the `load.json` endpoint:

```
https://myanimelist.net/animelist/{username}/load.json?status=7
```

This grabs your entire anime list in one request.

### Option 2: XML Upload

Export your anime list from MAL as XML and upload it directly.

### Field Mapping (JSON)

| MAL Field | TEAL Field |
|----------|-----------|
| `anime_id` | `mal_id` |
| `anime_title` | `title` |
| `anime_title_eng` | `original_title` |
| `anime_image_path` | `poster_url` |
| `anime_num_episodes` | `episodes_total` |
| `num_watched_episodes` | `episodes_watched` |
| `anime_media_type_string` | `media_type` |
| `anime_score_val` | `mal_score` |
| `score` | `rating` (1-10) |
| `start_date_string` | `date_started` |
| `finish_date_string` | `date_finished` |
| `tags` | `tags` |
| `genres` + `themes` | `genres` (merged) |

### Status Mapping

| MAL Status Code | TEAL Status |
|----------------|------------|
| 1 | Watching |
| 2 | Watched |
| 3, 4, 6 | Watchlist |
| Default | Watchlist |

### Media Type Normalization

MAL media types get normalized to: `TV`, `Movie`, `OVA`, `ONA`, `Special`, `Music`.

### Duplicate Detection

By `mal_id` only. If the anime already exists with the same MAL ID, the import skips it.

---

## ComicVine Search

**Route:** `/comics/search-comicvine`
**Service:** `App\Services\ComicVineService`

Comics are not imported from file. Instead, you search ComicVine directly from within TEAL, pick a volume, and import it along with its issues.

### How It Works

1. Search for a comic series (volume) by name.
2. TEAL queries the ComicVine API and shows matching results.
3. Select a volume to import it along with all its issues.
4. Issue details (cover art, descriptions, cover dates) are pulled in automatically.

### What Gets Imported

**Volume (Comic):**
- Title, publisher, start year, issue count
- Description, cover URL
- Creators and characters (first 20 each)

**Issues (ComicIssue):**
- Title, issue number, cover date
- Cover URL, description
- ComicVine issue ID

HTML in descriptions is automatically stripped and cleaned.
