# Welcome to TEAL

**TEAL** (The Essential Aggregator Library) is a self-hosted media tracker for books, comics, movies, and anime.

It lets you catalog, organize, and enrich metadata for everything you watch and read. Built with **Laravel 12**, **Livewire 3**, and **Tailwind CSS**.

---

## 🚀 Getting Started

### Tracking Movies & TV
1.  Navigate to **Watching > Movies & TV Shows**.
2.  Use **Search TMDB** to find metadata automatically.
3.  Update your **Status** (Watchlist, Watching, Watched) and add a **Rating**.

### Tracking Comics
1.  Navigate to **Reading > Comics**.
2.  Use the **ComicVine** search to find volumes and automatically import issue lists.

### Tracking Books
1.  Navigate to **Reading > Books**.
2.  Add books manually or import from Goodreads.
3.  TEAL fetches descriptions and cover art using **OpenLibrary** via ISBN.

---

## 📂 Documentation

### User Guides
- [[Importing Data]] -- Get your existing libraries into TEAL.
- [[Metadata Enrichment]] -- Automatically fill in missing details.
- [[Media Types]] -- Structure of models and statuses.
- [[Themes]] -- Customizing the look and feel.

### Technical & Contributor Guides
- [[Configuration]] -- Environment variables and server setup.
- [[For Developers]] -- Deep dive into the architecture and workflow.
- [[Docker Setup]] -- How to run a 1:1 replica of the production stack.
- [[API Integrations]] -- External services TEAL connects to.

---

## 🏗 Tech Stack
- **Backend:** Laravel 12 (PHP 8.4+)
- **Frontend:** Livewire 3 (SPA mode via `wire:navigate`)
- **Server:** FrankenPHP / Octane
- **Database:** PostgreSQL (Current) / PostgreSQL (Planned)
