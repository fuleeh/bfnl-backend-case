# Sport Venues API

Symfony 7.1 + API Platform REST API with JWT auth and location-based filtering.

## Requirements

- ✅ Symfony 7.1
- ✅ Large set of entities with pagination (default 30, max 100)
- ✅ lat/lng/distance filtering
- ✅ JSON response
- ✅ API Platform
- ✅ Unit and functional tests
- ✅ JWT authentication
- ✅ OpenAPI docs at `/api/docs`

## Includes

- Docker (PHP-FPM, Nginx, MySQL)
- SportVenue entity with geospatial filtering
- JWT authentication (register/login)

## Installation

```bash
composer install

# Generate JWT keys
mkdir -p config/jwt
openssl genrsa -out config/jwt/private.pem 4096
openssl rsa -pubout -in config/jwt/private.pem -out config/jwt/public.pem

# Start containers and run migrations
docker compose up -d
docker compose exec php-fpm php bin/console doctrine:migrations:migrate
docker compose exec php-fpm php bin/console doctrine:fixtures:load
```

## Endpoints

| Method | Path                | Auth |
| ------ | ------------------- | ---- |
| POST   | `/api/register`     | No   |
| POST   | `/api/login`        | No   |
| GET    | `/api/sport_venues` | Yes  |

## Filtering & Pagination

```
GET /api/sport_venues?lat=48.8566&lng=2.3522&distance=10&page=1
```

Location filtering uses MySQL's `ST_Distance_Sphere` — faster than doing the math in PHP and lets the database do what it's good at.

## Quick Example

```bash
curl -X POST http://localhost:49000/api/register \
  -H "Content-Type: application/json" \
  -d '{"email": "user@example.com", "password": "password123"}'

curl -X POST http://localhost:49000/api/login \
  -H "Content-Type: application/json" \
  -d '{"email": "user@example.com", "password": "password123"}'

curl http://localhost:49000/api/sport_venues \
  -H "Authorization: Bearer YOUR_TOKEN"
```

## Tests

```bash
docker compose exec php-fpm php vendor/bin/phpunit
```

## Docs

Interactive API docs at `http://localhost:49000/api/docs` — log in, click **Authorize** and paste your JWT token as `Bearer YOUR_TOKEN`.

> To test without location filters, clear the lat/lng/distance fields before executing.

## Project Structure

```
src/
├── Api/
│   └── SportVenues/
│       ├── ApiResource/    # API Platform resource definition
│       ├── Dto/            # Response DTOs
│       │   └── DtoMapper/  # Entity to DTO mapping
│       ├── Service/        # Business logic
│       └── State/          # Custom state provider
│           └── Provider/   # Provider implementation
├── Controller/             # Auth endpoints (login, register)
├── Entity/                 # Doctrine entities
└── Repository/             # Database queries
```

## Architecture

```
ApiResource → Provider → Service → Repository → Database
                ↓
              DTO ← Mapper
```

- Entity stays clean, API config lives in a separate `ApiResource` class
- DTO provides a stable response contract
- Custom state provider allows raw SQL for geospatial queries (`ST_Distance_Sphere`)
- Service layer separates business logic from data access
