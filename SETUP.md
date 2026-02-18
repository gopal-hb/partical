# PIRATICAL — Project Setup & Run Guide

> A Laravel 12 REST API that syncs posts from [JSONPlaceholder](https://jsonplaceholder.typicode.com/posts) and exposes them through authenticated, paginated endpoints using **Sanctum**, the **Repository Pattern**, and **Background Jobs**.

---

## Table of Contents

1. [Prerequisites](#prerequisites)
2. [Installation](#installation)
3. [Environment Configuration](#environment-configuration)
4. [Database Setup](#database-setup)
5. [Running the Application](#running-the-application)
6. [API Endpoints](#api-endpoints)
7. [Authentication Flow](#authentication-flow)
8. [Testing with Postman](#testing-with-postman)
9. [Running Tests](#running-tests)
10. [Project Architecture](#project-architecture)
11. [Troubleshooting](#troubleshooting)

---

## Prerequisites

Make sure the following are installed on your machine before proceeding:

| Tool        | Version   | Download Link                                      |
| ----------- | --------- | -------------------------------------------------- |
| **PHP**     | >= 8.2    | [php.net](https://www.php.net/downloads)           |
| **Composer**| >= 2.x    | [getcomposer.org](https://getcomposer.org/)        |
| **Node.js** | >= 18.x   | [nodejs.org](https://nodejs.org/)                  |
| **npm**     | >= 9.x    | Comes with Node.js                                 |
| **XAMPP**   | Latest    | [apachefriends.org](https://www.apachefriends.org/)|

> **Note:** This project uses **SQLite** by default — no MySQL/MariaDB setup is required.

### Verify Installations

```bash
php -v          # Should show PHP 8.2+
composer -V     # Should show Composer 2.x
node -v         # Should show v18+
npm -v          # Should show 9+
```

---

## Installation

### 1. Clone the Repository

```bash
git clone <repository-url>
cd partical
```

### 2. Install PHP Dependencies

```bash
composer install
```

### 3. Install Node.js Dependencies

```bash
npm install
```

### 4. Create Environment File

```bash
cp .env.example .env
```

### 5. Generate Application Key

```bash
php artisan key:generate
```

### Quick Setup (All-in-One)

Alternatively, you can run the composer setup script that handles steps 2–5 automatically:

```bash
composer run setup
```

---

## Environment Configuration

The project ships with a `.env.example` file. Key settings to review:

```env
# Application
APP_NAME=Laravel
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

# Database (SQLite — no extra setup needed)
DB_CONNECTION=sqlite

# Queue (uses database driver)
QUEUE_CONNECTION=database

# Cache
CACHE_STORE=file
```

> **Important:** The project uses **SQLite**. The database file is located at `database/database.sqlite`. If it doesn't exist, create it:
>
> ```bash
> # Windows (PowerShell)
> New-Item -Path database/database.sqlite -ItemType File -Force
>
> # macOS / Linux
> touch database/database.sqlite
> ```

---

## Database Setup

### Run Migrations

This creates the `users`, `posts`, `personal_access_tokens`, `cache`, and `jobs` tables:

```bash
php artisan migrate
```

### Seed the Database

This creates a default admin user for API authentication:

```bash
php artisan db:seed
```

**Default Admin Credentials:**

| Field      | Value                |
| ---------- | -------------------- |
| **Name**   | Admin User           |
| **Email**  | `admin@example.com`  |
| **Password** | `password123`      |

### Reset & Re-seed (if needed)

```bash
php artisan migrate:fresh --seed
```

---

## Running the Application

### Option 1: Run All Services Together (Recommended)

This starts the Laravel server, queue worker, and Vite dev server concurrently:

```bash
composer run dev
```

This runs:
- **Laravel Server** → `http://localhost:8000`
- **Queue Worker** → Processes background jobs (like post syncing)
- **Vite Dev Server** → Compiles frontend assets

### Option 2: Run Services Individually

Open **3 separate terminal windows** and run:

**Terminal 1 — Laravel Server:**
```bash
php artisan serve
```

**Terminal 2 — Queue Worker:**
```bash
php artisan queue:listen --tries=1
```

**Terminal 3 — Vite Dev Server:**
```bash
npm run dev
```

> ⚠️ **The Queue Worker is essential!** Without it, the background `SyncPostsJob` won't execute, and posts won't sync from the external API.

---

## API Endpoints

All API routes are prefixed with `/api`. The base URL is:

```
http://127.0.0.1:8000/api
```

### Public Endpoints

| Method | Endpoint          | Description                       |
| ------ | ----------------- | --------------------------------- |
| `POST` | `/api/login`      | Authenticate & get a Bearer token |
| `POST` | `/api/sync-posts` | Manually trigger post sync job    |

### Protected Endpoints (Require Bearer Token)

| Method | Endpoint              | Description                           |
| ------ | --------------------- | ------------------------------------- |
| `GET`  | `/api/v1/posts`       | List all posts (paginated, 10/page)   |
| `GET`  | `/api/v1/posts/{id}`  | Get a single post by external ID      |
| `GET`  | `/api/user`           | Get authenticated user details        |

> **Rate Limiting:** Protected endpoints are throttled at **60 requests per minute**.

---

## Authentication Flow

This project uses **Laravel Sanctum** for token-based API authentication.

### Step 1: Login to Get a Token

```bash
curl -X POST http://127.0.0.1:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{"email": "admin@example.com", "password": "password123"}'
```

**Response:**
```json
{
  "token": "1|abc123xyz456..."
}
```

### Step 2: Use the Token for Protected Routes

Include the token in the `Authorization` header:

```bash
curl -X GET http://127.0.0.1:8000/api/v1/posts \
  -H "Authorization: Bearer 1|abc123xyz456..."
```

---

## Testing with Postman

A Postman collection file is included in the project root:

```
PIRATICAL.postman_collection.json
```

### How to Import

1. Open **Postman**
2. Click **Import** (top left)
3. Drag & drop or browse for `PIRATICAL.postman_collection.json`
4. The collection **"PIRATICAL"** will appear in the sidebar

### Included Requests

| Request         | Method | URL                                       |
| --------------- | ------ | ----------------------------------------- |
| **Login**       | POST   | `http://127.0.0.1:8000/api/login`         |
| **Post**        | GET    | `http://127.0.0.1:8000/api/login`         |
| **Post details**| GET    | `http://127.0.0.1:8000/api/v1/posts/1`    |
| **Sync Posts**  | GET    | *(manual sync trigger)*                   |

### Postman Workflow

1. **Run the Login request** → Copy the `token` from the response
2. **Go to Post details** → In the **Authorization** tab, select **Bearer Token** and paste the token
3. **Send the request** → You'll see the post data

---

## Running Tests

The project uses **Pest** as the testing framework.

```bash
# Run all tests
php artisan test

# Or directly via Pest
./vendor/bin/pest

# Run with verbose output
php artisan test --verbose
```

---

## Project Architecture

```
partical/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   └── Api/
│   │   │       ├── AuthController.php      # Login & token generation
│   │   │       └── PostController.php       # Post listing & detail
│   │   └── Resources/
│   │       └── PostResource.php             # API response transformer
│   ├── Jobs/
│   │   └── SyncPostsJob.php                 # Background job for syncing posts
│   ├── Models/
│   │   ├── Post.php                         # Post model (external_id, title, body)
│   │   └── User.php                         # User model with Sanctum tokens
│   ├── Repositories/
│   │   └── PostRepository.php               # Data access layer for posts
│   └── Services/
│       └── ExternalPostService.php          # Fetches posts from JSONPlaceholder
├── database/
│   ├── migrations/                          # Database schema definitions
│   ├── seeders/
│   │   ├── DatabaseSeeder.php               # Main seeder
│   │   └── UserSeeder.php                   # Creates default admin user
│   └── database.sqlite                      # SQLite database file
├── routes/
│   ├── api.php                              # API route definitions
│   └── web.php                              # Web routes
├── tests/                                   # Pest test files
├── PIRATICAL.postman_collection.json        # Postman collection for API testing
├── AI_USAGE.md                              # AI tools usage disclosure
└── SETUP.md                                 # ← You are here
```

### Design Patterns Used

| Pattern              | Implementation                | Purpose                                           |
| -------------------- | ----------------------------- | ------------------------------------------------- |
| **Repository**       | `PostRepository.php`          | Abstracts database queries from business logic     |
| **Service Layer**    | `ExternalPostService.php`     | Encapsulates external API calls & sync logic       |
| **API Resources**    | `PostResource.php`            | Transforms Eloquent models into clean JSON         |
| **Background Jobs**  | `SyncPostsJob.php`            | Offloads external API sync to queue worker         |
| **Token Auth**       | Laravel Sanctum               | Stateless API authentication via Bearer tokens     |

### Data Flow

```
External API (JSONPlaceholder)
        │
        ▼
  ExternalPostService          ◄── Fetches posts via HTTP
        │
        ▼
    PostRepository             ◄── Saves/updates posts in DB (updateOrCreate)
        │
        ▼
     SQLite DB                 ◄── Stores posts locally
        │
        ▼
    PostController             ◄── Handles API requests
        │
        ▼
     PostResource              ◄── Formats JSON response
        │
        ▼
     Client (Postman/cURL)
```

---

## Troubleshooting

### "Posts are empty" when hitting `/api/v1/posts`

The posts need to be synced from the external API first. Either:

1. **Wait for auto-sync:** The first request to `/api/v1/posts` dispatches a `SyncPostsJob` automatically. Ensure the **queue worker is running** (`php artisan queue:listen`), then retry after a few seconds.

2. **Manual sync:**
   ```bash
   curl -X POST http://127.0.0.1:8000/api/sync-posts
   ```

### "Data is syncing, please try again shortly" (HTTP 202)

This means the sync job has been dispatched. Make sure:
- The **queue worker** is running: `php artisan queue:listen --tries=1`
- Wait a few seconds and retry

### "Invalid credentials" (HTTP 401)

- Make sure you ran `php artisan db:seed` to create the admin user
- Use email: `admin@example.com` and password: `password123`

### SQLite database errors

```bash
# Delete and recreate the database
del database\database.sqlite          # Windows
rm database/database.sqlite           # macOS/Linux

# Recreate
New-Item -Path database/database.sqlite -ItemType File   # Windows
touch database/database.sqlite                           # macOS/Linux

# Re-run migrations and seeder
php artisan migrate --seed
```

### Vite / npm errors

```bash
# Clear node_modules and reinstall
rm -rf node_modules
npm install
npm run dev
```

---

## Quick Start (TL;DR)

```bash
# 1. Clone & enter project
git clone <repository-url>
cd partical

# 2. Install dependencies
composer install
npm install

# 3. Setup environment
cp .env.example .env
php artisan key:generate

# 4. Setup database
php artisan migrate --seed

# 5. Run the application
composer run dev

# 6. Test the API
# Login:
curl -X POST http://127.0.0.1:8000/api/login \
  -d "email=admin@example.com&password=password123"

# Use returned token to fetch posts:
curl http://127.0.0.1:8000/api/v1/posts \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"
```

---

*Last updated: February 18, 2026*
