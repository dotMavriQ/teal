# Docker Setup

This guide explains how to spin up a 1:1 replica of the TEAL production environment using Docker. This ensures that you are developing against the same stack (FrankenPHP, Octane, Traefik subpath) used in production.

## 🚀 Quick Start

1.  **Clone the repository:**
    ```bash
    git clone https://github.com/dotMavriQ/TEAL-Laravel.git
    cd TEAL-Laravel
    ```

2.  **Setup Environment:**
    ```bash
    cp .env.example .env
    ```

3.  **Start the Containers:**
    We use `docker-compose.frankenphp.yml` for the application stack, which includes PostgreSQL and pgAdmin.
    ```bash
    docker compose -f docker-compose.frankenphp.yml up -d
    ```

4.  **Initialize the Application:**
    Run these inside the running container:
    ```bash
    # Install PHP dependencies
    docker exec teal-app-franken composer install

    # Generate App Key
    docker exec teal-app-franken php artisan key:generate

    # Run Migrations & Seed
    docker exec teal-app-franken php artisan migrate:fresh --seed
    ```

    You can access pgAdmin at `http://localhost:5050` (if mapped) or check the `docker-compose.frankenphp.yml` for port mappings.

5.  **Access the App:**
    Visit `http://localhost:8080`.

## 🛠 Working within Docker

### Running Artisan
```bash
docker exec -it teal-app-franken php artisan <command>
```

### Running Tests (Pest)
```bash
docker exec teal-app-franken php artisan test
```

### Container Shell
```bash
docker exec -it teal-app-franken bash
```

## 🔍 Troubleshooting

- **Subpath Issues:** If you need to test the `/teal` subpath specifically, ensure your `.env` has `APP_URL=http://localhost:8080/teal` and refer to the routing section in [[For Developers]].
- **Permissions:** If `storage` is unwritable:
  ```bash
  docker exec -u root teal-app-franken chown -R www-data:www-data /var/www/html/storage
  ```
