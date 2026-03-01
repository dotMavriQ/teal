# TEAL Developer Guide

Welcome to the TEAL development guide. This document explains the architecture and standards used in this project.

## Architecture

### API Integration (Saloon)
TEAL uses **Saloon v3** for all external API interactions. Instead of manual `Http` facade calls, we use "Connectors" and "Requests."

- **Connectors:** Located in `app/Services/Saloon/{Service}/{Service}Connector.php`. They handle base URLs, authentication, and global configurations.
- **Requests:** Located in `app/Services/Saloon/{Service}/Requests/*.php`. These are object-oriented representations of specific API endpoints.
- **Caching:** API responses are automatically cached using the `HasCaching` trait.
    - TMDB: 1 hour
    - ComicVine/Jikan: 24 hours
    - OpenLibrary: 7 days

### Theme System
TEAL uses a custom CSS-variable-based theme system built on top of Tailwind CSS.

- **Variables:** Defined in `resources/css/app.css` within `[data-theme="theme-name"]` blocks.
- **Tailwind Config:** `tailwind.config.js` maps these CSS variables to utility classes (e.g., `text-theme-text-primary`).
- **Components:** Always prefer `theme-` classes over standard Tailwind gray scales to ensure dark mode support.

### Database
- **Primary DB:** SQLite.
- **Migrations:** Be careful with migration ordering, especially when populating data from columns created in other migrations.

## Development Workflow
1.  **Tests:** Always run `php artisan test` before committing.
2.  **Branches:** Feature work should happen on specific branches (e.g., `refactor`).
3.  **Local Env:** Use `composer dev` to start the full stack (PHP, Vite, Queue, Logs).

## Docker Environment
For a 1:1 replica of the production environment, please refer to the [Docker Setup for Contributors](DOCKER_SETUP.md) guide.

## AI Assistance
This project includes a `GEMINI.md` file in the root directory. This file contains critical architectural insights and troubleshooting tips specifically designed to help AI assistants (like Gemini, GitHub Copilot, etc.) understand the project context quickly. If you are using an AI tool, point it to this file first.
