# Configuration

All configuration is done through environment variables in your `.env` file. No API keys are hardcoded anywhere.

## Required

These are needed for TEAL to function at all:

| Variable | Purpose |
|----------|---------|
| `APP_KEY` | Laravel encryption key. Generate with `php artisan key:generate` |
| `DB_CONNECTION` | Database driver. `sqlite` for local, `mysql` or `pgsql` for production |

## API Keys

All API integrations are optional. If a key is missing, that integration is simply disabled. You can use TEAL with zero API keys and add data manually.

### TMDB (Movies & TV Shows)

| Variable | Purpose | Required |
|----------|---------|----------|
| `TMDB_API_KEY` | v3 API key | One of these |
| `TMDB_ACCESS_TOKEN` | v4 bearer token | One of these |

TEAL tries the access token first (sent as a Bearer header), then falls back to the API key (sent as a query parameter). Either one works. Get both for free at [themoviedb.org/settings/api](https://www.themoviedb.org/settings/api).

### Trakt (Movies & TV Shows)

| Variable | Purpose | Required |
|----------|---------|----------|
| `TRAKT_CLIENT_ID` | Client ID from your Trakt app | Yes |

Register an app at [trakt.tv/oauth/applications](https://trakt.tv/oauth/applications) to get your client ID. TEAL only reads public data, so no OAuth flow is needed. The redirect URL during registration does not matter for our use case.

### ComicVine (Comics)

| Variable | Purpose | Required |
|----------|---------|----------|
| `COMIC_VINE_API_KEY` | API key | Yes |

Get a key at [comicvine.gamespot.com/api](https://comicvine.gamespot.com/api/).

### Jikan & OpenLibrary

No configuration needed. Both APIs are fully public with no authentication.

## Server

| Variable | Default | Purpose |
|----------|---------|---------|
| `OCTANE_SERVER` | `frankenphp` | Server runtime for Laravel Octane |

## Full `.env.example`

```env
APP_NAME=TEAL
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost

DB_CONNECTION=sqlite

SESSION_DRIVER=database
CACHE_STORE=database
QUEUE_CONNECTION=sync

TMDB_API_KEY=
TMDB_ACCESS_TOKEN=

TRAKT_CLIENT_ID=

COMIC_VINE_API_KEY=

OCTANE_SERVER=frankenphp
```

## Config Files

For reference, here is where each service is wired up in the Laravel config:

| Config Key | File | Maps To |
|-----------|------|---------|
| `services.tmdb.api_key` | `config/services.php` | `TMDB_API_KEY` |
| `services.tmdb.access_token` | `config/services.php` | `TMDB_ACCESS_TOKEN` |
| `services.trakt.client_id` | `config/services.php` | `TRAKT_CLIENT_ID` |
| `services.comic_vine.api_key` | `config/services.php` | `COMIC_VINE_API_KEY` |
