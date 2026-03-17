# BFNL back-end case skeleton

Includes:

- Docker
- Partner entity

## Installation

```shell
composer install
docker compose up -d
docker compose exec php-fpm bin/console doctrine:migrations:migrate
docker compose exec php-fpm bin/console doctrine:fixtures:load
```
