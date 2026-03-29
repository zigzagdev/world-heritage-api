# World Heritage API

Backend API for a world heritage study app targeting users preparing for the World Heritage Certification exam.
Fetches and manages UNESCO World Heritage data, providing APIs for search, listing, and detail retrieval.

Persona
This API powers a study app for people preparing for the World Heritage Certification Exam.
Instead of carrying a heavy textbook, users can quickly look up heritage sites — their overview, location, and classification — from their smartphone or PC. The app targets anyone interested in the certification, regardless of age or experience.
Example User
Tanaka-san, 28, office worker
Decided to take the World Heritage Certification Exam (Level 2). During the commute, she wants to quickly look up heritage sites on her phone, but the official textbook is too heavy to carry around. She needs a tool where she can check the location on a map along with the category and year of inscription — all in one place.

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