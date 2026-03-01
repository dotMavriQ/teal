# TEAL Project Roadmap

## 🚀 The Backlog
- [ ] **Modernize the Server (FrankenPHP + Laravel Octane)**
    - *Goal:* Make the app feel significantly faster (up to 80% speed boost) by keeping it in memory.
    - *Tasks:*
        - Update the Docker setup to use FrankenPHP.
        - Install and configure Laravel Octane.
        - Check for any memory leaks during long-running imports.
- [ ] **Build a Comprehensive Test Suite (Pest)**
    - *Goal:* Make sure we can refactor things without breaking the app.
    - *Tasks:*
        - Write "Mock" tests for all the new Saloon API clients.
        - Create feature tests for the core library (Adding/Editing/Deleting items).

## 🏃 In Progress
- [ ] **Switching to FrankenPHP:** Moving away from the traditional Nginx/PHP-FPM setup to a modern, unified server.

## ✅ Completed
- [x] **Technical Audit & Optimization (PR #15):**
    - **Security: SQL Injection Mitigation**
        - *Problem:* User-controlled sort parameters were being interpolated directly into raw SQL queries.
        - *Solution:* Implemented strict allowlists for `sortBy` and `sortDirection` and transitioned to `orderBy(DB::raw(...))` with validated inputs.
    - **Performance: N+1 Query Elimination in Imports**
        - *Problem:* Mass imports triggered thousands of individual `exists()` checks.
        - *Solution:* Optimized all import services (`GoodReads`, `IMDb`, `MAL`, `JSON`) to pre-load record identifiers into memory maps, reducing database roundtrips to just a few per batch.
    - **Performance: Dashboard Query Consolidation**
        - *Problem:* The Dashboard executed 8+ separate count queries on every load.
        - *Solution:* Consolidated stats gathering into 4 optimized queries using conditional aggregation, improving load times.
    - **Bug Fix: Dashboard Statistic Accuracy**
        - *Problem:* "Read this year" book stats were failing due to a non-existent column reference.
        - *Solution:* Updated query logic to use the correct `date_recorded` column.
- [x] **Comics Module:** Initial implementation of comic tracking and ComicVine search.
- [x] **API Modernization (Saloon):** 
    - Moved all external API calls (Movies, Comics, Anime, Books) into a structured, type-safe system.
    - **Smart Caching:** Implemented a system that saves API results locally to speed up the UI. 
        - *Movies:* 1-hour cache.
        - *Comics/Anime:* 24-hour cache.
        - *Books:* 7-day cache.
