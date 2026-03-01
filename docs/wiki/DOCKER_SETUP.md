# Docker Setup for Contributors

This guide explains how to spin up a 1:1 replica of the TEAL production environment using Docker. This ensures that you are developing against the same stack (FrankenPHP, Octane, Redis/Cache) used in production.

## Prerequisites

- [Docker Engine](https://docs.docker.com/engine/install/) & Docker Compose (usually included)
- Git

## Quick Start

1.  **Clone the repository:**
    ```bash
    git clone https://github.com/dotMavriQ/TEAL-Laravel.git
    cd TEAL-Laravel
    ```

2.  **Setup Environment:**
    Copy the example environment file.
    ```bash
    cp .env.example .env
    ```
    *Note: You may need to fill in API keys for TMDB or Comic Vine if you want to test those features.*

3.  **Start the Container:**
    We use `docker-compose.frankenphp.yml` for the application stack.
    ```bash
    docker compose -f docker-compose.frankenphp.yml up -d
    ```

4.  **Install Dependencies & Initialize:**
    Since this is the first run, we need to install PHP dependencies and run migrations inside the container.
    ```bash
    # Install Composer dependencies
    docker exec teal-app-franken composer install

    # Generate App Key
    docker exec teal-app-franken php artisan key:generate

    # Run Migrations
    docker exec teal-app-franken php artisan migrate:fresh --seed

    # Install & Build Frontend Assets (Node/Vite)
    docker exec teal-app-franken npm install
    docker exec teal-app-franken npm run build
    ```

5.  **Access the App:**
    Open your browser and visit `http://localhost:8080`.

## Working with the Container

### Running Artisan Commands
Always run artisan commands inside the container to ensure they use the correct PHP version and extensions.
```bash
docker exec -it teal-app-franken php artisan <command>
```

### Running Tests
```bash
docker exec teal-app-franken php artisan test
```

### Accessing the Shell
To get a bash shell inside the running container:
```bash
docker exec -it teal-app-franken bash
```

### Troubleshooting
- **Ports:** If port `8080` is in use, modify the `ports` section in `docker-compose.frankenphp.yml`.
- **Permissions:** If you encounter permission issues with `storage/` or `bootstrap/cache/`, run:
  ```bash
  docker exec -u root teal-app-franken chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
  ```
