# Metadata Enrichment

After importing your library, many entries will be missing details like descriptions, posters, genres, or directors. Metadata enrichment fills in those gaps by querying external APIs.

Each media type has its own enrichment flow, but the core idea is always the same: check what fields are empty, query external sources, and fill in what you can without overwriting existing data.

## Table of Contents

- [Movies & TV Shows](#movies--tv-shows)
- [Anime](#anime)
- [Books](#books)
- [How Source Priority Works](#how-source-priority-works)
- [The Merge Strategy](#the-merge-strategy)

---

## Movies & TV Shows

**Route:** `/movies/settings/metadata`
**Component:** `App\Livewire\Movies\MovieMetadataEnrichment`
**Batch Job:** `App\Jobs\FetchMovieMetadata`

### Sources (in default order)

1. **Current Values** -- What you already have. Highest priority by default.
2. **Trakt** -- Good for descriptions, genres, and runtime. Particularly useful for non-English content that TMDB might not cover well.
3. **TMDB** -- The most complete source overall. Best for posters, directors, and credits.

You can reorder these in the UI. See [How Source Priority Works](#how-source-priority-works) for details.

### Enrichable Fields

| Field | Trakt | TMDB |
|-------|-------|------|
| Description | Yes | Yes |
| Poster | Yes (lower quality) | Yes (preferred) |
| Runtime | Yes | Yes |
| Release Date | Yes | Yes |
| Genres | Yes | Yes |
| Director | No | Yes |

### Scan & Fetch

The enrichment page has two steps:

1. **Scan Library** -- Queries your database for movies with at least one empty enrichable field. Results are sorted with the most incomplete entries at the top.
2. **Fetch Metadata** -- Queries Trakt and TMDB for each movie and merges the results.

You can fetch in bulk (up to 100 at a time) or review entries one by one.

### Lookup Strategy

For movies and TV shows that are not episodes:

1. If the entry has an **IMDb ID**, look it up by ID on each source.
2. If no IMDb ID exists, fall back to **title search**.
3. If the entry has an IMDb ID but the lookup fails (transient API error, unlisted title), **stop**. Do not fall back to title search.

That last point is intentional. Searching "Batman: The Dark Knight Returns, Part 1" by title can easily match the wrong Batman movie and give you the wrong poster. If we have an authoritative identifier and it does not resolve, it is better to skip than to guess.

### Episode Handling

TV Episodes get special treatment:

1. Look up the episode by IMDb ID on TMDB to get the parent show's name, season number, episode number, and poster.
2. If that fails, extract the show name from the episode title (everything before the first colon) and search for the show poster by title.
3. When a poster is found, propagate it to all sibling episodes that share the same show name or title prefix.

This means fixing one episode's poster often fixes dozens of related episodes automatically.

### Rate Limiting

The batch job waits **300ms** between TMDB API calls to stay within rate limits.

---

## Anime

**Route:** `/anime/settings/metadata`
**Component:** `App\Livewire\Anime\AnimeMetadataEnrichment`

### Source

Anime metadata comes from a single source: **Jikan** (an unofficial MyAnimeList API).

### Enrichable Fields

| Field | Jikan |
|-------|-------|
| Description (synopsis) | Yes |
| Poster | Yes |
| Runtime | Yes |
| Genres | Yes |
| Studios | Yes |
| Episodes Total | Yes |
| Media Type | Yes |
| Original Title | Yes |
| Year | Yes |
| MAL Score | Yes |
| MAL URL | Yes |

### Lookup Strategy

1. If the anime has a **MAL ID**, look it up directly.
2. If not, search by **title**.

### Batch Processing

Anime enrichment processes up to **50 entries** per batch. Unlike movies, this runs inline (no background job) since Jikan is a single source.

### Rate Limiting

The Jikan service enforces a **400ms** delay between requests to respect the API's rate limits.

---

## Books

**Route:** `/books/settings/metadata`
**Component:** `App\Livewire\Books\MetadataEnrichment`
**Batch Job:** `App\Jobs\FetchBookMetadata`

### Source

Book metadata comes from **OpenLibrary**.

### Requirements

Only books that have an **ISBN or ISBN13** can be enriched. Books without ISBNs are skipped.

### Enrichable Fields

| Field | OpenLibrary |
|-------|------------|
| Description | Yes |
| Publisher | Yes |
| Page Count | Yes |
| Published Date | Yes |

### Rate Limiting

The batch job waits **250ms** between OpenLibrary API calls.

---

## How Source Priority Works

On the Movies enrichment page, you can reorder the sources: **Current Values**, **Trakt**, and **TMDB**.

The first source in the list gets highest priority. Here is what that means:

- **"Current Values" first (default):** External sources only fill in fields that are completely empty. Nothing you already have gets touched.
- **"Trakt" or "TMDB" first:** External data overwrites your existing values when available. Useful if you suspect your current data is wrong and want to replace it wholesale.

Most of the time you want "Current Values" on top. The other order is there for bulk corrections.

---

## The Merge Strategy

When multiple external sources are configured (like Trakt + TMDB), TEAL queries all of them and merges the results field by field.

The first source in priority order that has a non-empty value for a given field wins. Later sources only fill in fields that earlier sources left empty.

**Example with default priority (Current > Trakt > TMDB):**

| Field | Current | Trakt | TMDB | Result |
|-------|---------|-------|------|--------|
| Description | _(empty)_ | "A ticking-time-bomb..." | "An insomniac office worker..." | Trakt wins |
| Poster | _(empty)_ | _(empty)_ | `tmdb.org/w500/abc.jpg` | TMDB wins |
| Director | _(empty)_ | _(empty)_ | "David Fincher" | TMDB wins |
| Runtime | 139 | 139 | 139 | Current kept (not empty) |
| Genres | _(empty)_ | "Drama, Thriller" | "Drama" | Trakt wins |

The result: you get descriptions and genres from Trakt (often more detailed), posters and directors from TMDB (which always has those), and everything you already had stays in place.

This is why Trakt is useful even though it never has directors: it fills in the fields it is good at, and TMDB covers the rest.
