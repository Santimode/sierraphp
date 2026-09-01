# AIHANDOFF.md — sierraPHP

> Version: 2.2.0 | Repo: Santimode/sierraphp | Package: santimode/sierraphp | Updated: 2026-09-01
> Brand: sierraPHP | Namespace: Sierra\ | PHP: ^8.2

This file is the single source of truth for AI agents.

### 1. Snapshot
- Repo: https://github.com/Santimode/sierraphp (public, active — MVP scaffold + exception handling done)
- Current state: Container, Router, Http (Request/Response/HttpException), Middleware (Stack, LogMiddleware), Exceptions\Handler all implemented and wired. `composer test` passes (10 tests, 15 assertions) for Container/Request/Router. `.env.example` and `phpunit.xml` committed, so `composer install && cp .env.example .env` works cleanly on a fresh clone.
- Goal: Build MVP framework — router + container + http + middleware — in < 50ms (met); now hardening toward production-readiness (see Section 7)

### 2. Naming — FINAL
- Filesystem: `sierraphp` lowercase everywhere
- Display: `sierraPHP`
- Composer: `santimode/sierraphp`
- Do NOT use V2 in filename. Version lives in file header (this header).

### 3. MVP Done Criteria
```php
Route::get('/hello/{name}', function(Request $req, string $name) {
    return response()->json(['hello' => $name]);
})->middleware(LogMiddleware::class);
```
Tests: router param ✅, middleware chain ✅ (LogMiddleware implemented, no dedicated test yet), container auto-wire ✅, json response ✅, 404 ✅ (covered via RouterTest, not exception-based)

**Not yet covered:** `Exceptions\Handler` and `Http\HttpException` have zero tests. This is the top testing gap — see Section 7.

### 4. Architecture Decisions
- fast-route for dispatch, wrapped in Sierra\Router\Router
- Simple PSR-11 container with auto-wiring
- Minimal Request/Response (not full PSR-7, but compatible ideas)
- Middleware Stack is PSR-15-like
- Application is glue

### 5. File Map (v2.2.0 Scaffold)
- src/Application.php — creates container, router, runs dispatch; wraps dispatch in try/catch, delegates to Exceptions\Handler on Throwable
- src/Container/Container.php — bind/singleton/get/has/make
- src/Router/Route.php — value object
- src/Router/Router.php — collection + group + fast-route integration
- src/Http/Request.php — from globals, input(), query(), param bag
- src/Http/Response.php — json(), view(), send()
- src/Http/HttpException.php — RuntimeException + statusCode, for throwing intentional HTTP errors from route/controller code
- src/Exceptions/Handler.php — report() (error_log), renderDebug() (Whoops if installed, else built-in HTML fallback), renderProduction() (generic JSON, preserves HttpException status code)
- src/Middleware/* — MiddlewareInterface, Stack, LogMiddleware (logs method/uri/status/timing, sets X-Sierra-Time header)
- src/Support/helpers.php — app(), response(), request(), view(), config(), env()
- public/index.php — front controller
- routes/web.php — example routes
- tests/Feature/{ContainerTest,RequestTest,RouterTest}.php — Pest, 10 tests / 15 assertions
- .env.example, phpunit.xml — required for a clean fresh-clone setup, now committed

### 6. Versioning Inside File (your request)
Instead of `README-V2.md`, use:
```
Version: 2.2.0
Last Updated: 2026-09-01
Changelog at bottom of file
```
Clean filenames: README.md, AIHANDOFF.md, AGENTS.md

### 7. Next Steps After Scaffold
1. ✅ composer install
2. ✅ php -S localhost:8000 -t public
3. ✅ Implement Exceptions/Handler for pretty errors (debug + production modes, Whoops fallback, wired into Application::run())
4. ✅ Add Pest tests (Container, Request, Router — Handler/HttpException still untested, see #5 below)

**Remaining before production-ready:**
5. Write Pest tests for `Exceptions\Handler` and `Http\HttpException` (debug render, production render, status-code preservation)
6. Add content-negotiation to `Handler` — respond with JSON when `Accept: application/json`, HTML otherwise, in both debug and production modes
7. Add an `abort(int $status, string $message = '')` helper in `Support/helpers.php` that throws `HttpException` — currently `HttpException` exists but nothing in the app throws it
8. Swap `error_log()` in `Handler::report()` for a pluggable logger (or at minimum write to `storage/logs/` when the directory exists)
9. Add a GitHub Actions workflow running `composer test` on push/PR — no CI currently enforces the test suite
10. Confirm `filp/whoops` belongs in `composer.json` `require-dev` explicitly — it's present in `composer.lock` but not listed directly in `Composer.json`, which is a drift risk

---
Changelog:
- 2.2.0: Exception handling shipped (Handler + HttpException), LogMiddleware added, .env.example + phpunit.xml committed, first Pest suite passing (10/10). Updated snapshot, done-criteria, file map, and next steps to match actual repo state; added remaining gaps (#5-10) toward production-readiness.
- 2.1.1: Added scaffold details, clarified version-in-file strategy
- 2.0.0: Lowercase repo decision
- 1.0.0: Initial
