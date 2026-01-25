# TEAL

A personal book library management app built with Laravel and Livewire.

## Features

- **Import from Goodreads** - Import your existing library from Goodreads CSV export
- **Book tracking** - Track reading status (Want to Read, Reading, Read), ratings, and dates
- **Gallery & List views** - Browse your library in a visual grid or sortable table
- **Metadata enrichment** - Automatically fetch book covers and details from OpenLibrary
- **Shelves & Tags** - Organize books with custom shelves and tags
- **Notes & Reviews** - Add personal notes and reviews to books

## Requirements

- PHP 8.2+
- Composer
- Node.js & npm

## Installation

```bash
# Clone the repo
git clone https://github.com/yourusername/shit.git
cd shit

# Install dependencies
composer install
npm install

# Setup environment
cp .env.example .env
php artisan key:generate

# Create database and run migrations
touch database/database.sqlite
php artisan migrate

# Build assets
npm run build

# Start the server
php artisan serve
```

## Usage

1. Register an account at `/register`
2. Import your Goodreads library or add books manually
3. Track your reading progress and organize with shelves

## Tech Stack

- Laravel 11
- Livewire 3
- Tailwind CSS
- SQLite

## License

MIT
