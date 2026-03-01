# For Developers

Welcome to the TEAL development guide. This document explains the architecture, standards, and workflow used in this project.

## 🏗 Architecture

### Controller-less Design
TEAL uses a "no-controller" architecture. All routes in `routes/web.php` point directly to Livewire components.
- **Components:** `app/Livewire/`
- **Views:** `resources/views/livewire/`
- **Benefit:** Logic cohesion, simplified request lifecycle, and SPA-like navigation via `wire:navigate`.

### Bleeding Edge Dependency Policy
During the pre-beta phase, TEAL prioritizes the latest stable releases of all dependencies.
- **PHP Version:** PHP >= 8.4 (Locked by Symfony 8.x requirements).
- **Core Stance:** Do not downgrade dependencies (e.g., Symfony, Saloon, Laravel) to support older PHP versions (8.2/8.3) until the project reaches Beta status.
- **Octane-First:** Optimized for Laravel Octane + FrankenPHP. Ensure all code is "state-safe."

### Interactivity & State Persistence
- **Alpine.js First:** Use Alpine.js for non-database UI toggles (e.g., view modes) to reduce server round-trips. Entangle with Livewire if the state needs to persist in the URL.
- **Livewire Pagination:** Always use `wire:click` handlers (`gotoPage`, `nextPage`, etc.) in pagination templates. Using standard `href` links will trigger a full page reload and wipe the Livewire component state (filters, search, etc.).

### API Integration (Saloon)
We use **Saloon v3** for all external API interactions.
- **Connectors:** `app/Services/Saloon/{Service}/{Service}Connector.php`
- **Requests:** `app/Services/Saloon/{Service}/Requests/*.php`
- **Caching:** Responses are cached via the `HasCaching` trait (TMDB: 1h, others: 24h+).

### Theme System
Custom CSS-variable-based system on top of Tailwind.
- **Definitions:** `resources/css/app.css`
- **Usage:** Always use `theme-` utility classes (e.g., `text-theme-text-primary`) instead of standard Tailwind colors.

### Database
- **Current:** PostgreSQL (default).
- **Migration from SQLite:** Use `php artisan app:migrate-sqlite-to-pgsql` to transfer data from a local `database/database.sqlite` file to the configured PostgreSQL instance.

## 🛠 Getting Started

### Prerequisites
- PHP 8.4+
- Composer & Node.js/npm
- Docker (Recommended for production-parity)

### Docker Setup (Highly Recommended)
For a 1:1 replica of the production stack (FrankenPHP, Octane, subpath routing), follow the [[Docker Setup]] guide.

### Manual Local Setup
```bash
git clone https://github.com/dotMavriQ/TEAL-Laravel.git
cd TEAL-Laravel
composer install && npm install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate
npm run build
php artisan serve
```

## 🧪 Testing & CI

### Running Tests
TEAL uses [Pest](https://pestphp.com/).
```bash
php artisan test
# or
./vendor/bin/pest
```

### CI Pipeline
GitHub Actions runs the full suite on every push to `main`. It builds assets and runs migrations on a fresh SQLite instance.

## 🤝 Contributing

### Code Style
- Follow existing patterns in Livewire components.
- Use Enums (`label()`, `color()`) in views.
- **Shift Left Security:** This repo uses `pre-commit` with `gitleaks`. Staging a secret will block your commit.

### AI Assistance
Refer to `GEMINI.md` in the root for deep architectural insights designed for AI tools.

## 🔗 Links
- [Issues](https://github.com/dotMavriQ/TEAL-Laravel/issues)
- [Project Kanban](https://github.com/users/dotMavriQ/projects/1)
