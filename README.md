# TEAL

**The Essential Aggregator Library** -- a self-hosted media tracker for books, comics, movies, and anime.

<p align="center">
  <img src="https://blog.dotmavriq.life/_astro/tealscreenshot.DnHem90M.png" alt="TEAL Screenshot" width="800">
</p>

Built with Laravel 12, Livewire 3, and Tailwind CSS. Uses PostgreSQL by default.

## What it does

- Track books, comics, movies, and anime with status, ratings, dates, and notes
- Import from Goodreads (CSV), IMDb (CSV), and MyAnimeList (XML export / username)
- Search and add comics from Comic Vine, with per-issue tracking (volume/issue hierarchy)
- Fetch metadata and covers from OpenLibrary, TMDB, Jikan (MAL), and Comic Vine
- Gallery and list views with search, filtering, and sorting
- Reading queue for books
- Two themes out of the box (light and Gruvbox Dark)
- Single-user, per-account data isolation via policies

## Setup

Requires PHP 8.4+, Composer, Node.js, and npm.

```bash
git clone https://github.com/dotMavriQ/TEAL-Laravel.git
cd TEAL-Laravel
composer setup
```

`composer setup` handles dependency installation, `.env` creation, key generation, migrations, and asset building.

To start a dev server with queue worker, log tailing, and Vite:

```bash
composer dev
```

Or just the basics:

```bash
php artisan serve
```

Register an account at `/register` and you're in.

## External services (optional)

Movie metadata uses TMDB. If you want it, grab an API key from [themoviedb.org](https://www.themoviedb.org/settings/api) and add it to `.env`:

```
TMDB_API_KEY=your_key
TMDB_ACCESS_TOKEN=your_token
```

Comic search and metadata uses Comic Vine. Grab an API key from [comicvine.gamespot.com](https://comicvine.gamespot.com/api/) and add it to `.env`:

```
COMIC_VINE_API_KEY=your_key
```

Book metadata (OpenLibrary) and anime metadata (Jikan/MAL) work without API keys.

## Development

### Coding standard

The codebase follows **PSR-12** (and, by extension, PSR-1 for basic style and PSR-4 for autoloading). Style is enforced with [Laravel Pint](https://laravel.com/docs/pint) using the `laravel` preset — a PSR-12 superset that adds Laravel idioms — configured in `pint.json`. Every PHP file declares `strict_types=1`.

```bash
composer lint      # check formatting (pint --test), no changes
./vendor/bin/pint  # apply formatting
```

### Static analysis

[PHPStan](https://phpstan.org/) via [Larastan](https://github.com/larastan/larastan) runs at `level: max`, configured in `phpstan.neon`. Existing findings are captured in `phpstan-baseline.neon`; new code must not add to the baseline. Declared-type coverage floors (return/param/property) ratchet up over time and cannot regress.

```bash
composer stan      # phpstan analyse
composer quality   # lint + stan
```

A `pre-push` hook (`.githooks/pre-push`, enabled via `composer hooks`) runs Pint and PHPStan before every push. Tests run in CI.

```bash
composer test      # Pest suite (needs the teal_test database)
```

## License

MIT
