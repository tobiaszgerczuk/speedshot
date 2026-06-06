# speedshot

Local WordPress project for `speedshot.localhost`.

## Setup

```sh
cp .env.example .env
docker compose up -d
```

The project expects the shared local Traefik network named `web`.

## URLs

- Site: https://speedshot.localhost
- phpMyAdmin: https://pma.speedshot.localhost

## WP-CLI

```sh
docker compose run --rm wp-cli wp core version
```
