# API Integrations

TEAL connects to five external APIs for metadata lookups. All of them use the [Saloon PHP](https://docs.saloon.dev/) HTTP client, which gives us connection pooling, caching, and structured request/response handling.

No API requires OAuth for TEAL's use case. They all work with simple API keys.

## Table of Contents

- [TMDB (The Movie Database)](#tmdb)
- [Trakt](#trakt)
- [Jikan (MyAnimeList)](#jikan)
- [ComicVine](#comicvine)
- [OpenLibrary](#openlibrary)

---

## TMDB

**Used for:** Movies, TV Shows, Episodes
**Base URL:** `https://api.themoviedb.org/3`
**Documentation:** [developer.themoviedb.org](https://developer.themoviedb.org/docs)

### Authentication

TMDB supports two auth methods. TEAL tries the access token first, then falls back to the API key:

| Method | Header/Param | Env Variable |
|--------|-------------|-------------|
| Bearer token (preferred) | `Authorization: Bearer {token}` | `TMDB_ACCESS_TOKEN` |
| API key (fallback) | `?api_key={key}` | `TMDB_API_KEY` |

Get both for free at [themoviedb.org/settings/api](https://www.themoviedb.org/settings/api).

### Endpoints Used

| Endpoint | Purpose |
|----------|---------|
| `GET /find/{imdb_id}` | Look up any title by IMDb ID |
| `GET /movie/{id}` | Full movie details with credits |
| `GET /tv/{id}` | Full TV show details with credits and seasons |
| `GET /tv/{id}/season/{n}` | Episode list for a season |
| `GET /search/multi` | Search movies and TV shows by title |
| `GET /search/movie` | Search movies by title (with year filter) |
| `GET /search/tv` | Search TV shows by title |

### Caching

Responses are cached for **1 hour** using Laravel's cache store.

### Rate Limiting

TEAL waits **300ms** between requests during batch operations.

### Image URLs

All poster paths from TMDB are relative. TEAL prepends `https://image.tmdb.org/t/p/w500` to get the full URL. The `w500` size is a good balance between quality and bandwidth.

### Genre Normalization

TMDB uses some compound genre names that TEAL splits:

| TMDB Genre | TEAL Genres |
|-----------|------------|
| Action & Adventure | Action, Adventure |
| Sci-Fi & Fantasy | Sci-Fi, Fantasy |
| War & Politics | War |
| Science Fiction | Sci-Fi |

### Code Location

| File | Purpose |
|------|---------|
| `app/Services/Saloon/Tmdb/TmdbConnector.php` | Connector (auth, base URL, caching) |
| `app/Services/Saloon/Tmdb/Requests/*.php` | Individual request classes |
| `app/Services/TmdbService.php` | High-level service with normalization |

---

## Trakt

**Used for:** Movies, TV Shows
**Base URL:** `https://api.trakt.tv`
**Documentation:** [trakt.docs.apiary.io](https://trakt.docs.apiary.io/)

### Authentication

Trakt uses a client ID passed as a header. No OAuth needed for public data.

| Header | Value |
|--------|-------|
| `trakt-api-key` | Your client ID |
| `trakt-api-version` | `2` |
| `Content-Type` | `application/json` |

Get your client ID by registering an app at [trakt.tv/oauth/applications](https://trakt.tv/oauth/applications).

**Env variable:** `TRAKT_CLIENT_ID`

### Endpoints Used

| Endpoint | Purpose |
|----------|---------|
| `GET /search/imdb/{id}` | Look up by IMDb ID |
| `GET /search/{type}?query={q}` | Search by title |

Both endpoints use `?extended=full,images` to get descriptions, runtime, and poster images in a single call.

### What Trakt Is Good At

Trakt tends to have solid descriptions, accurate runtimes, and genre data. It is particularly useful for non-English and niche content where TMDB might be sparse.

### What Trakt Does Not Have

Trakt does not return director or crew information in search results. This is why TMDB remains valuable as a secondary source even when Trakt is the primary.

### Poster Quality

Trakt posters are lower resolution than TMDB. When both sources have a poster, the merge strategy will prefer whichever source is higher in your priority list. Since TMDB posters are generally better, keeping TMDB below Trakt in priority still gives you TMDB posters (because Trakt fills in description/runtime first, and TMDB fills in the poster if Trakt's is empty or lower quality).

In practice, most entries end up with TMDB posters and Trakt descriptions. Best of both worlds.

### Code Location

| File | Purpose |
|------|---------|
| `app/Services/Saloon/Trakt/TraktConnector.php` | Connector (auth, base URL) |
| `app/Services/Saloon/Trakt/Requests/*.php` | Request classes |
| `app/Services/TraktService.php` | Service with normalization |

---

## Jikan

**Used for:** Anime
**Base URL:** `https://api.jikan.moe/v4`
**Documentation:** [docs.api.jikan.moe](https://docs.api.jikan.moe/)

Jikan is an unofficial REST API for MyAnimeList. It wraps MAL's data and makes it accessible without needing MAL API credentials.

### Authentication

None. Jikan is fully public with no API key required.

### Endpoints Used

| Endpoint | Purpose |
|----------|---------|
| `GET /anime/{malId}` | Full anime details by MAL ID |
| `GET /anime?q={query}` | Search anime by title |

### Caching

Responses are cached for **24 hours** since anime metadata rarely changes.

### Rate Limiting

Jikan enforces a **400ms** delay between requests. This is handled automatically by `JikanService`.

### Runtime Parsing

Jikan returns duration as a human-readable string like `"1 hr 30 min"` or `"24 min per ep"`. TEAL parses this into minutes:

- `"1 hr 30 min"` -> 90
- `"24 min per ep"` -> 24
- `"2 hr"` -> 120

### Code Location

| File | Purpose |
|------|---------|
| `app/Services/Saloon/Jikan/JikanConnector.php` | Connector (base URL, caching) |
| `app/Services/Saloon/Jikan/Requests/*.php` | Request classes |
| `app/Services/JikanService.php` | Service with normalization |

---

## ComicVine

**Used for:** Comics
**Base URL:** `https://comicvine.gamespot.com/api`
**Documentation:** [comicvine.gamespot.com/api/documentation](https://comicvine.gamespot.com/api/documentation)

### Authentication

API key passed as a query parameter.

**Env variable:** `COMIC_VINE_API_KEY`

Get a key at [comicvine.gamespot.com/api](https://comicvine.gamespot.com/api/).

### Endpoints Used

| Endpoint | Purpose |
|----------|---------|
| `GET /search/?query={q}&resources=volume` | Search comic volumes by title |
| `GET /volume/4050-{id}/` | Full volume details |
| `GET /issues/?filter=volume:{id}` | Paginated issue list (100 per page) |

All requests include `format=json` in the query string.

### Caching

Responses are cached for **24 hours**.

### Rate Limiting

**400ms** delay between paginated requests when fetching issue lists.

### HTML Stripping

ComicVine descriptions come back as HTML. TEAL strips tags, decodes entities, and normalizes whitespace before storing.

### Code Location

| File | Purpose |
|------|---------|
| `app/Services/Saloon/ComicVine/ComicVineConnector.php` | Connector |
| `app/Services/Saloon/ComicVine/Requests/*.php` | Request classes |
| `app/Services/ComicVineService.php` | Service |

---

## OpenLibrary

**Used for:** Books
**Base URL:** `https://openlibrary.org`
**Documentation:** [openlibrary.org/developers/api](https://openlibrary.org/developers/api)

### Authentication

None. OpenLibrary is fully public.

### Endpoints Used

| Endpoint | Purpose |
|----------|---------|
| `GET /isbn/{isbn}.json` | Look up a book by ISBN |
| `GET /works/{workKey}.json` | Get work-level data (for descriptions) |

OpenLibrary has a two-level data model: editions (specific printings) and works (the abstract book). TEAL fetches the edition first by ISBN, then optionally fetches the work for a description if the edition does not have one.

### Caching

Responses are cached for **7 days** since book metadata almost never changes.

### Rate Limiting

**250ms** delay between requests during batch operations.

### ISBN Normalization

Before querying, ISBNs are cleaned by removing all non-alphanumeric characters (except `X`, which is valid in ISBN-10 check digits).

### Code Location

| File | Purpose |
|------|---------|
| `app/Services/Saloon/OpenLibrary/OpenLibraryConnector.php` | Connector |
| `app/Services/Saloon/OpenLibrary/Requests/*.php` | Request classes |
| `app/Services/OpenLibraryService.php` | Service |
