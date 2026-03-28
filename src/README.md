# World Heritage API

Backend API for a world heritage study app targeting users preparing for the World Heritage Certification exam.
Fetches and manages UNESCO World Heritage data, providing APIs for search, listing, and detail retrieval.

## Tech Stack

- PHP / Laravel
- MySQL
- Algolia (Full-text search)
- Koyeb (Production hosting)

## Getting Started

```bash
cp .env.example .env
composer install
php artisan key:generate
php artisan migrate
```

## Building Data in Production

Run the following commands from the Koyeb console.

### Initial / Full rebuild

Dumps data from UNESCO and rebuilds the DB and Algolia index.

```bash
php artisan app:world-heritage-build --force --dump --jp --pretty
```

| Option | Description |
|---|---|
| `--force` | Allow execution outside local/testing |
| `--dump` | Dump JSON from UNESCO |
| `--jp` | Import Japanese names |
| `--pretty` | Pretty print JSON output |

### Full rebuild including DB and Algolia (with table reset)

```bash
php artisan app:world-heritage-build --fresh --jp --pretty --algolia --algolia-truncate --force
```

> ⚠️ `--fresh` drops and recreates all tables. All existing data will be lost.

### Re-import Japanese names only

```bash
php artisan world-heritage:import-japanese-names --force
```

## Testing

```bash
php artisan test
```