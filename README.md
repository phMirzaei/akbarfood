# Akbarfood - Laravel 12 Docker Project

Dockerized Laravel 12 application with PHP-FPM, Nginx, MariaDB, and phpMyAdmin.

## Prerequisites

- Docker (20.10+)
- Docker Compose (2.0+)

## Setup

1. Clone the repository:
```bash
git clone <repository-url>
cd akbarfood
```

2. Copy environment file:
```bash
cp .env.example .env
```

3. Build and start containers:
```bash
docker-compose up -d --build
```

4. Install dependencies:
```bash
docker-compose exec app composer install
```

5. Generate application key:
```bash
docker-compose exec app php artisan key:generate
```

6. Run migrations:
```bash
docker-compose exec app php artisan migrate
```

## Access

- **Application**: http://localhost
- **phpMyAdmin**: http://localhost:8080
