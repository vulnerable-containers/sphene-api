# Sphene API

Sphene Backend API written in Laravel. This service is used to accommodate file downloading for Sphene and as the API for benchmarks. Future usages will include processing debugging data.

## How to run

- Copy .env.example to .env. Add any desired values.
- Copy your GTA San Andreas installation into `storage/cdn/data/game/san_andreas` (exact list coming!).
- `docker compose up -d`

## TODO

Database migrations for benchmarks are missing at this time.