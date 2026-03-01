# Media Types

TEAL organizes media into categories and subcategories. Each media type has its own model, status enum, and set of fields.

## Table of Contents

- [Status Enums](#status-enums)
- [Movies](#movies)
- [Shows](#shows)
- [Anime](#anime)
- [Books](#books)
- [Comics](#comics)

---

## Status Enums

Two enums drive status tracking across all media types. Both are backed string enums with `label()` and `color()` methods.

### ReadingStatus

Used by Books and Comics.

| Value | Label | Color |
|-------|-------|-------|
| `want_to_read` | Want to Read | Blue |
| `reading` | Currently Reading | Yellow |
| `read` | Read | Green |

### WatchingStatus

Used by Movies, Shows, and Anime.

| Value | Label | Color |
|-------|-------|-------|
| `watchlist` | Watchlist | Purple |
| `watching` | Watching | Yellow |
| `watched` | Watched | Green |

### Using Enums in Code

```php
use App\Enums\WatchingStatus;

$movie->status = WatchingStatus::Watched;
$movie->status->label(); // "Watched"
$movie->status->color(); // "green"
```

Status colors map to CSS variables: `--color-status-{name}` and `--color-status-{name}-bg`.

---

## Movies

**Model:** `App\Models\Movie`
**Policy:** `App\Policies\MoviePolicy`
**Table:** `movies`

The Movies table stores movies, TV movies, shorts, specials, and TV episodes. It is the most versatile table in TEAL since IMDb exports dump all of these into a single list.

### Key Fields

| Field | Type | Notes |
|-------|------|-------|
| `title` | string | Required |
| `original_title` | string | For non-English titles |
| `director` | string | Comma-separated for multiple |
| `imdb_id` | string | e.g. `tt0137523` |
| `poster_url` | string | Full URL to poster image |
| `description` | text | Plot summary |
| `year` | integer | Release year |
| `runtime_minutes` | integer | |
| `genres` | string | Comma-separated |
| `title_type` | string | From IMDb: Movie, TV Series, TV Episode, etc. |
| `status` | WatchingStatus | |
| `rating` | integer | 1-10 |
| `imdb_rating` | float | From IMDb |
| `date_watched` | date | |
| `show_name` | string | Parent show (for episodes) |
| `season_number` | integer | For episodes |
| `episode_number` | integer | For episodes |
| `metadata_fetched_at` | datetime | Tracks enrichment |

### Episode Detection

A Movie record is considered an episode if:
- Both `season_number` and `episode_number` are set, OR
- `title_type` is `TV Episode`

### Poster Propagation

When a TV Series entry gets a poster, TEAL propagates it to all episodes that share the same `show_name` or title prefix (the part before the first colon). Only episodes with empty `poster_url` fields are updated.

### View Modes

- **Gallery view:** 18 items per page, poster grid
- **List view:** 25 items per page, tabular

### Search

Search is accent-insensitive. TEAL normalizes both the query and stored titles using `Str::ascii()` in PHP before comparing.

---

## Shows

**Model:** `App\Models\Show`
**Policy:** `App\Policies\ShowPolicy`
**Table:** `shows`

Shows are TV Series and TV Mini Series entries created during IMDb import. They serve as parent records for episodes stored in the Movies table.

### Key Fields

| Field | Type | Notes |
|-------|------|-------|
| `title` | string | |
| `original_title` | string | |
| `imdb_id` | string | |
| `poster_url` | string | |
| `description` | text | |
| `year` | integer | |
| `genres` | string | |
| `status` | WatchingStatus | |
| `rating` | integer | 1-10 |
| `imdb_rating` | float | |
| `date_added` | date | |

Shows are accessed through the Movies & TV Shows section. There is no separate Shows UI.

---

## Anime

**Model:** `App\Models\Anime`
**Policy:** `App\Policies\AnimePolicy`
**Table:** `anime`

### Key Fields

| Field | Type | Notes |
|-------|------|-------|
| `title` | string | |
| `original_title` | string | Japanese title |
| `poster_url` | string | |
| `description` | text | Synopsis |
| `year` | integer | |
| `episodes_total` | integer | |
| `episodes_watched` | integer | |
| `runtime_minutes` | integer | Per episode |
| `genres` | string | Comma-separated |
| `studios` | string | Comma-separated |
| `media_type` | string | TV, Movie, OVA, ONA, Special, Music |
| `status` | WatchingStatus | |
| `rating` | integer | 1-10 |
| `mal_id` | integer | MyAnimeList ID |
| `mal_score` | float | MAL community score |
| `mal_url` | string | |
| `date_started` | date | |
| `date_finished` | date | |
| `tags` | string | |
| `notes` | text | |

---

## Books

**Model:** `App\Models\Book`
**Policy:** `App\Policies\BookPolicy`
**Table:** `books`

### Key Fields

| Field | Type | Notes |
|-------|------|-------|
| `title` | string | |
| `author` | string | |
| `isbn` | string | ISBN-10 |
| `isbn13` | string | ISBN-13 |
| `asin` | string | Amazon identifier |
| `cover_url` | string | |
| `description` | text | |
| `page_count` | integer | |
| `current_page` | integer | Reading progress |
| `published_date` | date | |
| `publisher` | string | |
| `goodreads_id` | string | |
| `status` | ReadingStatus | |
| `rating` | integer | 1-5 stars |
| `date_started` | date | |
| `date_recorded` | date | When finished reading |
| `notes` | text | |
| `review` | text | |

### Rating Scale

Books use a **1-5 star** rating, unlike Movies and Anime which use 1-10.

### Shelves

Books can belong to custom Shelves (many-to-many via `book_shelf` pivot). Shelves are created automatically during JSON imports and can be managed manually.

---

## Comics

**Model:** `App\Models\Comic`
**Table:** `comics`

Comics represent volumes (series), and each volume has issues.

### Volume Fields

| Field | Type | Notes |
|-------|------|-------|
| `title` | string | |
| `publisher` | string | |
| `start_year` | integer | |
| `issue_count` | integer | |
| `description` | text | |
| `cover_url` | string | |
| `comicvine_volume_id` | integer | |
| `status` | ReadingStatus | |
| `rating` | integer | |
| `creators` | string | Comma-separated |
| `characters` | string | Comma-separated |

### Issues

**Model:** `App\Models\ComicIssue`
**Table:** `comic_issues`

| Field | Type | Notes |
|-------|------|-------|
| `comic_id` | integer | Parent volume |
| `title` | string | |
| `issue_number` | string | |
| `cover_date` | date | |
| `cover_url` | string | |
| `description` | text | |
| `comicvine_issue_id` | integer | |
| `status` | ReadingStatus | |
| `rating` | integer | |
| `date_read` | date | |

---

## Date Conventions

All dates are stored as `YYYY-MM-DD` in the database using Carbon date casting.

In the UI, dates are displayed and entered as **DD/MM/YYYY**. The `parseDateInput()` helper converts between the two formats.

For imports, year-only dates (common in book publishing) are stored as `YYYY-01-01`.
