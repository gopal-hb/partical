# PIRATICAL — Laravel REST API

> A production-ready REST API built with **Laravel 12** that fetches posts from [JSONPlaceholder](https://jsonplaceholder.typicode.com/posts), stores them locally, and exposes clean, paginated, authenticated endpoints.

---

## Table of Contents

- [Setup Instructions](#setup-instructions)
- [API Documentation](#api-documentation)
- [Architecture Overview](#architecture-overview)
- [Key Decisions & Trade-offs](#key-decisions--trade-offs)
- [Known Limitations](#known-limitations)
- [Bonus Features Implemented](#bonus-features-implemented)

---

## External API Used

**JSONPlaceholder** — Free fake REST API for testing and prototyping.

- **URL:** [https://jsonplaceholder.typicode.com/posts](https://jsonplaceholder.typicode.com/posts)
- **Docs:** [https://jsonplaceholder.typicode.com/guide/](https://jsonplaceholder.typicode.com/guide/)
- Publicly accessible, well-documented, no API key required.

---

## Setup Instructions

### Prerequisites

| Tool         | Version  |
| ------------ | -------- |
| **PHP**      | >= 8.2   |
| **Composer** | >= 2.x   |
| **Node.js**  | >= 18.x  |
| **npm**      | >= 9.x   |

> This project uses **SQLite** — no MySQL/MariaDB setup required.

### Step-by-Step Installation

```bash
# 1. Clone the repository
git clone <repository-url>
cd partical

# 2. Install PHP dependencies
composer install

# 3. Install Node.js dependencies
npm install

# 4. Create environment file
cp .env.example .env

# 5. Generate application key
php artisan key:generate

# 6. Create SQLite database (if not exists)
# Windows (PowerShell):
New-Item -Path database/database.sqlite -ItemType File -Force
# macOS / Linux:
touch database/database.sqlite

# 7. Run migrations and seed the database
php artisan migrate --seed

# 8. Start the application (runs server + queue worker + vite)
composer run dev
```

The application will be available at: **http://127.0.0.1:8000**

### Default Login Credentials

| Field      | Value               |
| ---------- | ------------------- |
| Email      | `admin@example.com` |
| Password   | `password123`       |

> ⚠️ **Important:** The **queue worker must be running** for background post syncing to work. The `composer run dev` command handles this automatically.

---

## API Documentation

**Base URL:** `http://127.0.0.1:8000/api`

### Authentication

This API uses **Laravel Sanctum** (Bearer Token). You must login first to access protected endpoints.

---

### `POST /api/login` — Get Auth Token

**Request:**
```bash
curl -X POST http://127.0.0.1:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{"email": "admin@example.com", "password": "password123"}'
```

**Success Response (200):**
```json
{
  "token": "1|abc123xyz456..."
}
```

**Error Response (401):**
```json
{
  "message": "Invalid credentials"
}
```

---

### `GET /api/v1/posts` — List All Posts (Paginated)

🔒 **Requires Bearer Token**

**Request:**
```bash
curl http://127.0.0.1:8000/api/v1/posts \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"
```

**Success Response (200):**
```json
{
  "data": [
    {
      "id": 1,
      "title": "sunt aut facere repellat provident occaecati...",
      "body": "quia et suscipit\nsuscipit recusandae consequuntur...",
      "created_at": "2026-02-18T04:50:22.000000Z"
    },
    {
      "id": 2,
      "title": "qui est esse",
      "body": "est rerum tempore vitae\nsequi sint nihil...",
      "created_at": "2026-02-18T04:50:22.000000Z"
    }
  ],
  "links": {
    "first": "http://127.0.0.1:8000/api/v1/posts?page=1",
    "last": "http://127.0.0.1:8000/api/v1/posts?page=10",
    "prev": null,
    "next": "http://127.0.0.1:8000/api/v1/posts?page=2"
  },
  "meta": {
    "current_page": 1,
    "last_page": 10,
    "per_page": 10,
    "total": 100
  }
}
```

**Loading State Response (202):** *(When posts are syncing for the first time)*
```json
{
  "message": "Data is syncing, please try again shortly."
}
```

**Unauthenticated Response (401):**
```json
{
  "message": "Unauthenticated."
}
```

---

### `GET /api/v1/posts/{id}` — Get Post by ID

🔒 **Requires Bearer Token**

**Request:**
```bash
curl http://127.0.0.1:8000/api/v1/posts/1 \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"
```

**Success Response (200):**
```json
{
  "data": {
    "id": 1,
    "title": "sunt aut facere repellat provident occaecati...",
    "body": "quia et suscipit\nsuscipit recusandae consequuntur...",
    "created_at": "2026-02-18T04:50:22.000000Z"
  }
}
```

**Not Found Response (404):**
```json
{
  "message": "Post not found"
}
```

---

### `POST /api/sync-posts` — Manually Trigger Sync

**Request:**
```bash
curl -X POST http://127.0.0.1:8000/api/sync-posts
```

**Response (200):**
```json
{
  "message": "Sync started"
}
```

---

### Postman Collection

A ready-to-use Postman collection is included:

```
📄 PIRATICAL.postman_collection.json
```

Import it into Postman to test all endpoints immediately.

---

## Architecture Overview

### Project Structure

```
app/
├── Http/
│   ├── Controllers/Api/
│   │   ├── AuthController.php       → Login & token generation
│   │   └── PostController.php       → List & detail endpoints
│   └── Resources/
│       └── PostResource.php         → JSON response transformer
├── Jobs/
│   └── SyncPostsJob.php             → Background sync job
├── Models/
│   ├── Post.php                     → Post model (external_id, title, body)
│   └── User.php                     → User model with Sanctum tokens
├── Repositories/
│   └── PostRepository.php           → Data access layer
└── Services/
    └── ExternalPostService.php      → External API integration
```

### Design Patterns

| Pattern            | File                        | Purpose                                                |
| ------------------ | --------------------------- | ------------------------------------------------------ |
| **Repository**     | `PostRepository.php`        | Abstracts all database queries away from controllers    |
| **Service Layer**  | `ExternalPostService.php`   | Encapsulates external HTTP calls and sync logic         |
| **API Resources**  | `PostResource.php`          | Transforms Eloquent models into clean, consistent JSON  |
| **Background Jobs**| `SyncPostsJob.php`          | Offloads external API fetching to queue worker          |

### Data Flow

```
JSONPlaceholder API
        │
        ▼
  ExternalPostService        ← Fetches 100 posts via HTTP Client
        │
        ▼
    PostRepository           ← updateOrCreate into SQLite
        │
        ▼
     SQLite DB               ← Local cache of external data
        │
        ▼
    PostController           ← Handles authenticated API requests
        │
        ▼
     PostResource            ← Clean JSON transformation
        │
        ▼
     Client Response
```

### Error & State Handling

| State         | HTTP Code | Response                                          |
| ------------- | --------- | ------------------------------------------------- |
| **Loading**   | `202`     | `"Data is syncing, please try again shortly."`    |
| **Empty/404** | `404`     | `"Post not found"`                                |
| **Auth fail** | `401`     | `"Unauthenticated."` or `"Invalid credentials"`  |
| **API error** | Handled   | `ExternalPostService` catches exceptions silently, returns `false` |
| **Success**   | `200`     | Paginated JSON via `PostResource`                 |

---

## Key Decisions & Trade-offs

### 1. SQLite over MySQL
- **Decision:** Used SQLite as the default database.
- **Why:** Zero configuration needed. Reviewers can clone and run immediately without setting up MySQL/MariaDB.
- **Trade-off:** SQLite doesn't support concurrent writes well, but for this API's scope (read-heavy, single sync job), it's sufficient.

### 2. Repository Pattern
- **Decision:** Created `PostRepository` to handle all database queries.
- **Why:** Keeps controllers thin. If the data source changes (e.g., switching from Eloquent to raw queries or another ORM), only the repository needs modification.
- **Trade-off:** Adds a layer of abstraction that may seem like over-engineering for a small project, but demonstrates separation of concerns.

### 3. Background Job for Syncing (over synchronous fetch)
- **Decision:** Used `SyncPostsJob` dispatched to queue instead of fetching data synchronously on each request.
- **Why:** Prevents slow responses when the external API is down or slow. The first request returns a `202` immediately while data syncs in the background.
- **Trade-off:** Requires a running queue worker. Added `composer run dev` script to handle this automatically.

### 4. Cache-Based Sync Guard
- **Decision:** Used `Cache::has('posts_last_sync')` to prevent duplicate sync jobs.
- **Why:** Without this, every request to an empty database would dispatch a new sync job. The cache key expires after 1 hour, allowing periodic re-syncs.
- **Trade-off:** If the cache is cleared manually, a re-sync may trigger unnecessarily — but this is acceptable.

### 5. Sanctum over Passport
- **Decision:** Used Laravel Sanctum for API authentication.
- **Why:** Lightweight, built-in, and sufficient for token-based API auth. Passport is designed for full OAuth2 which is overkill here.
- **Trade-off:** No OAuth2 features (client credentials, scopes), but those aren't needed for this use case.

### 6. `updateOrCreate` for Data Persistence
- **Decision:** Used `updateOrCreate` keyed on `external_id` in the repository.
- **Why:** Ensures idempotent syncs — running the sync multiple times doesn't create duplicate records. Updates existing data if the external source changes.
- **Trade-off:** Slightly slower than bulk insert, but guarantees data integrity.

---

## Known Limitations

1. **No automated tests for custom logic** — Only default Pest example tests are included. Feature tests for auth flow and post endpoints would improve confidence.
2. **No retry mechanism** — If `SyncPostsJob` fails, it doesn't automatically retry with exponential backoff.
3. **Single external API** — Hardcoded to JSONPlaceholder. Could be made configurable via `.env` for flexibility.
4. **No filter/search on posts** — The list endpoint doesn't support query parameters for filtering by title or body.
5. **No logout/token revocation endpoint** — Once a token is issued, it persists until manually deleted.
6. **Queue worker dependency** — The background sync requires a running queue worker. If the worker stops, sync jobs pile up in the database.

### What I Would Improve With More Time

- Add feature tests for authentication, post listing, pagination, error states
- Add a scheduled command (`php artisan schedule:run`) to auto-sync posts periodically
- Implement token expiry and a logout endpoint
- Add search/filter query parameters on the posts list endpoint
- Add Docker setup for one-command deployment
- Implement proper logging for sync failures
- Add request validation classes (Form Requests)

---

## Bonus Features Implemented

| Bonus Feature             | Status | Details                                         |
| ------------------------- | ------ | ----------------------------------------------- |
| API Authentication        | ✅     | Laravel Sanctum (Bearer Token)                  |
| Rate Limiting             | ✅     | 60 requests/minute on protected endpoints       |
| Background Jobs           | ✅     | `SyncPostsJob` via queue worker                 |
| API Versioning            | ✅     | Routes prefixed with `/api/v1/`                 |
| Postman Collection        | ✅     | `PIRATICAL.postman_collection.json` included    |

---

## Running Tests

```bash
php artisan test
# or
./vendor/bin/pest
```

---

## Tech Stack

| Technology        | Version |
| ------------------| ------- |
| Laravel           | 12.x   |
| PHP               | 8.2+   |
| Laravel Sanctum   | 4.x    |
| SQLite            | Default |
| Vite              | 7.x    |
| Tailwind CSS      | 4.x    |
| Pest (Testing)    | 3.x    |

---

*Built as a take-home assignment for Laravel Developer position.*
