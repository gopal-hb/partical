# AI Usage Documentation

## AI Tools Used

- **ChatGPT** — Used for architecture planning, code guidance, and design pattern suggestions
- **Gemini (Google AI)** — Used for project documentation generation (`SETUP.md`)

---

## Where AI Was Used

### 1. Architecture Planning & Design Decisions

**AI Assisted:** Yes

AI was consulted for overall project architecture decisions, including:

- Choosing the **Repository Pattern** (`PostRepository.php`) to separate data access logic from controllers
- Designing the **Service Layer** (`ExternalPostService.php`) to encapsulate external API integration
- Structuring the API with **versioned routes** (`/api/v1/posts`) for future scalability
- Selecting **Laravel Sanctum** for token-based API authentication over Passport (lighter weight for this use case)

### 2. Repository Pattern Implementation

**AI Assisted:** Yes (guidance) &nbsp;|&nbsp; **Manually Written:** Yes (final code)

- `app/Repositories/PostRepository.php` — AI suggested using the Repository pattern to abstract Eloquent queries. The implementation (`getPaginated`, `findByExternalId`, `saveMany`, `count`) was written manually following that guidance.

### 3. Service Layer Design

**AI Assisted:** Yes (guidance) &nbsp;|&nbsp; **Manually Written:** Yes (final code)

- `app/Services/ExternalPostService.php` — AI recommended separating external HTTP calls into a dedicated service class. The sync logic (HTTP call → repository save → cache timestamp) was implemented based on this recommendation.

### 4. Error Handling & Exception Rendering

**AI Assisted:** Yes (improved approach)

- `bootstrap/app.php` — Custom exception rendering for `AuthenticationException` was improved with AI assistance to return proper JSON responses for API routes instead of redirecting to a login page.

### 5. Background Job Architecture

**AI Assisted:** Yes (pattern suggestion)

- `app/Jobs/SyncPostsJob.php` — AI suggested using Laravel's queue system with a dedicated job for syncing posts, combined with a cache check (`Cache::has('posts_last_sync')`) to prevent duplicate syncs.

### 6. Documentation Generation

**AI Assisted:** Yes (fully generated)

- `SETUP.md` — Project setup and run documentation was generated with AI assistance (Gemini), based on scanning the actual project structure.

---

## What Was Modified After AI Suggestions

| Component | AI Suggested | What I Changed |
|---|---|---|
| **Controller design** | AI initially suggested putting all logic in controllers (fat controllers) | **Rejected** — Moved logic to Repository + Service layers |
| **Error handling** | Basic try-catch only | **Improved** — Added HTTP timeout (10s), cache-based sync tracking, graceful failure returns |
| **Post syncing** | Sync on every request | **Improved** — Uses background `SyncPostsJob` + cache check to avoid redundant API calls |
| **Authentication** | AI suggested using Passport | **Modified** — Used Sanctum instead, simpler and sufficient for this API |
| **API response format** | Return raw Eloquent models | **Improved** — Added `PostResource` transformer for clean, consistent JSON output |

---

## What Was Rejected From AI

| Suggestion | Reason for Rejection |
|---|---|
| **Fat controller approach** | Violated separation of concerns; repository + service pattern is cleaner |
| **Syncing data on every API request** | Performance concern; replaced with queue-based background job |
| **Storing API tokens in `.env`** | Not applicable; using Sanctum's database token approach instead |
| **Using Laravel Passport** | Overkill for this project; Sanctum is lighter and meets all requirements |

---

## Code Written Entirely Without AI

The following components were written manually without AI assistance:

- **Database Migrations** — `create_posts_table`, `create_personal_access_tokens_table`
- **Database Seeders** — `UserSeeder.php`, `DatabaseSeeder.php`
- **Model Definitions** — `Post.php`, `User.php` (including `HasApiTokens` trait)
- **API Route Definitions** — `routes/api.php` (route grouping, middleware, prefixing)
- **Postman Collection** — `PIRATICAL.postman_collection.json`
- **Environment Configuration** — `.env` setup for SQLite, queue, cache
- **Vite Configuration** — `vite.config.js`
- **Rate Limiting** — Custom rate limiter in `bootstrap/app.php`

---

## Summary

| Category | AI Used? | Details |
|---|---|---|
| Architecture planning | ✅ Yes | Pattern selection, layer separation |
| Repository pattern | ✅ Partial | AI guided the pattern; code written manually |
| Service layer | ✅ Partial | AI guided the approach; code written manually |
| Error handling | ✅ Partial | AI improved the approach; manually implemented |
| Background jobs | ✅ Partial | AI suggested the pattern; manually implemented |
| Database schema | ❌ No | Migrations written manually |
| Seeders | ❌ No | Written manually |
| API routing | ❌ No | Written manually |
| Authentication setup | ❌ No | Sanctum integration done manually |
| Postman collection | ❌ No | Created manually |
| Project documentation | ✅ Yes | `SETUP.md` generated with AI |

---

*Last updated: February 18, 2026*
