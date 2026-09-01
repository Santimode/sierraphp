# sierraPHP

> Light as the Sierra Madre, powerful as it needs to be.

A minimalist PHP framework inspired by **Slim's speed** and **Laravel's elegance**.

**Version:** 2.2.0  
**Repo:** `Santimode/sierraphp` — https://github.com/Santimode/sierraphp  
**Package:** `santimode/sierraphp`  
**Last Updated:** 2026-09-01  
**Status:** MVP scaffolded, core hardening in progress — not production-ready yet (see Known Gaps)

---

### Philosophy
1. Light by default: < 20 core files
2. Slim-inspired: FastRoute, PSR-15 middleware, front controller
3. Laravel-inspired: `Route::get()`, `app()`, `response()->json()`, auto-wiring container
4. No magic: PSR-12, PHP 8.2+ strict types

### Quick Start
```bash
git clone https://github.com/Santimode/sierraphp.git
cd sierraphp
composer install
cp .env.example .env
php -S localhost:8000 -t public
```

Visit http://localhost:8000

### Target API (now working)
```php
Route::get('/', fn() => view('welcome', ['name' => 'Santi']));
Route::get('/api/health', fn() => response()->json(['status' => 'ok']));
Route::get('/users/{id}', [UserController::class, 'show'])->middleware(LogMiddleware::class);
```

### Testing
```bash
composer test
```
Runs the Pest suite (`ContainerTest`, `RequestTest`, `RouterTest` — 10 tests, 15 assertions as of 2.2.0). `phpunit.xml` and `.env.example` are committed and required for a clean `composer install` + first run.

### Error Handling
Uncaught exceptions and errors are caught centrally in `Application::run()` and passed to `Sierra\Exceptions\Handler`:
- **Debug mode** (`APP_DEBUG=true`): renders a pretty error page via Whoops if installed, otherwise a built-in dark-themed HTML fallback with escaped stack trace.
- **Production mode** (`APP_DEBUG=false`): renders a generic JSON error, preserving the status code if the thrown exception is a `Sierra\Http\HttpException`.

Both modes currently always respond in one fixed format (HTML for debug, JSON for production) regardless of the client's `Accept` header — see Known Gaps.

### Project Structure
```
sierraphp/
├── src/
│   ├── Application.php
│   ├── Container/Container.php
│   ├── Router/
│   ├── Http/
│   │   ├── Request.php
│   │   ├── Response.php
│   │   └── HttpException.php
│   ├── Exceptions/Handler.php
│   ├── Middleware/
│   │   ├── MiddlewareInterface.php
│   │   ├── Stack.php
│   │   └── LogMiddleware.php
│   └── Support/
├── public/index.php
├── routes/web.php
├── config/app.php
├── tests/
├── phpunit.xml
├── .env.example
└── composer.json
```

### Naming
- Repo/Composer: `santimode/sierraphp` (lowercase)
- Brand: `sierraPHP`
- Namespace: `Sierra\`

### Known Gaps (tracked in AIHANDOFF.md)
- No tests yet for `Exceptions\Handler` or `Http\HttpException`
- No content-negotiation in the error handler (JSON vs HTML based on `Accept` header)
- No `abort()` helper — `HttpException` exists but isn't ergonomic to throw from routes yet
- Logging is `error_log()` only, no swappable/structured logger
- No CI workflow running `composer test` on push/PR

### Changelog (inside file versioning)
- 2.2.0 (2026-09-01): Added `Exceptions\Handler` + `Http\HttpException` (wired into `Application::run()`), `LogMiddleware`, `.env.example`, `phpunit.xml`, and the first Pest tests (Container, Request, Router — 10 passing)
- 2.1.1 (2026-09-01): Cleaned duplicate code, fixed `Request` immutability
- 2.1.0 (2026-09-01): Initial MVP scaffold — Container, Router, Request/Response, Middleware Stack
- 2.0.0 (2026-09-01): Docs updated to lowercase repo
- 1.0.0 (2026-09-01): Initial docs

### License
MIT © Santimode
