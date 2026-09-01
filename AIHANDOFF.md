# AIHANDOFF.md — sierraPHP

> Version: 2.4.0 | Repo: Santimode/sierraphp | Package: santimode/sierraphp | Updated: 2026-09-01
> Brand: sierraPHP | Namespace: Sierra\ | PHP: ^8.2

This file is the single source of truth for AI agents.

### 1. Snapshot
- Repo: https://github.com/Santimode/sierraphp (public, active — MVP scaffold + full HTTP verbs & router completeness + test suite done)
- Current state: Container, Router (GET/POST/PUT/PATCH/DELETE/OPTIONS/HEAD/any/match/group middleware), Http (Request with method spoofing, isMethod, isJson, expectsJson, header / Response / HttpException), Middleware (Stack, LogMiddleware), Exceptions\Handler, and helpers all implemented and tested. `composer test` passes (36 tests, 106 assertions). `.env.example` and `phpunit.xml` committed.
- Goal: Build MVP framework — router + container + http + middleware — in < 50ms (met); now hardening toward production-readiness (see Section 7)

### 2. Naming — FINAL
- Filesystem: `sierraphp` lowercase everywhere
- Display: `sierraPHP`
- Composer: `santimode/sierraphp`
- Do NOT use V2 in filename. Version lives in file header (this header).

### 3. MVP Done Criteria
```php
Route::put('/items/{id}', function(Request $req, string $id) {
    return response()->json(['updated' => $id]);
})->middleware(LogMiddleware::class);
```
Tests: full HTTP verbs (GET, POST, PUT, PATCH, DELETE, OPTIONS, HEAD, match, any) ✅, method spoofing (_method & X-HTTP-Method-Override) ✅, group middleware & prefixes ✅, middleware chain ✅, container auto-wire ✅, json response ✅, 404/405 ✅, exceptions debug/production ✅, abort() helper ✅ (36 tests / 106 assertions)

### 4. Architecture Decisions
- fast-route for dispatch, wrapped in Sierra\Router\Router
- Simple PSR-11 container with auto-wiring
- Minimal Request/Response (not full PSR-7, but compatible ideas)
- Middleware Stack is PSR-15-like
- Application is glue

### 5. File Map (v2.4.0 Scaffold)
- src/Application.php — creates container, router, runs dispatch; forwards all router verbs, wraps dispatch in try/catch, delegates to Exceptions\Handler on Throwable
- src/Container/Container.php — bind/singleton/get/has/make
- src/Router/Route.php — value object
- src/Router/Router.php — get/post/put/patch/delete/options/head/match/any/group with middleware and prefix support
- src/Http/Request.php — from globals with method spoofing, header(), isMethod(), isJson(), expectsJson(), input(), query(), param bag
- src/Http/Response.php — json(), view(), send(), getStatusCode(), getBody(), getHeader(), getHeaders()
- src/Http/HttpException.php — RuntimeException + statusCode, for throwing intentional HTTP errors from route/controller code
- src/Exceptions/Handler.php — report() (error_log), renderDebug() (Whoops if installed, else built-in HTML fallback), renderProduction() (generic JSON, preserves HttpException status code)
- src/Middleware/* — MiddlewareInterface, Stack, LogMiddleware (logs method/uri/status/timing, sets X-Sierra-Time header)
- src/Support/helpers.php — abort(), app(), response(), request(), view(), config(), env()
- src/Support/Facades/Route.php — static facade for Router
- public/index.php — front controller
- routes/web.php — example routes
- tests/Feature/{ContainerTest,RequestTest,RouterTest,HttpExceptionTest,HandlerTest,HelperTest,MiddlewareTest}.php — Pest, 36 tests / 106 assertions
- .env.example, phpunit.xml — required for a clean fresh-clone setup, committed

### 6. Versioning Inside File (your request)
Instead of `README-V2.md`, use:
```
Version: 2.4.0
Last Updated: 2026-09-01
Changelog at bottom of file
```
Clean filenames: README.md, AIHANDOFF.md, AGENTS.md

### 7. Next Steps
1. ✅ composer install
2. ✅ php -S localhost:8000 -t public
3. ✅ Implement Exceptions/Handler for pretty errors (debug + production modes, Whoops fallback, wired into Application::run())
4. ✅ Add Pest tests (Container, Request, Router)
5. ✅ Write Pest tests for `Exceptions\Handler`, `Http\HttpException`, `LogMiddleware`, and `Stack`
6. ✅ Add `abort(int $status, string $message = '')` helper in `Support/helpers.php` and test it
7. ✅ Explicitly track `filp/whoops` in `composer.json` `require-dev`
8. ✅ Full HTTP verbs (PUT, PATCH, DELETE, OPTIONS, HEAD, match, any), group middleware inheritance, and request method spoofing

**Remaining before production-ready:**
9. Add content-negotiation to `Handler` — respond with JSON when `Accept: application/json` or `expectsJson()`, HTML otherwise, in both debug and production modes
10. Swap `error_log()` in `Handler::report()` for a pluggable logger (or at minimum write to `storage/logs/` when the directory exists)
11. Add security middleware (CORS, Security Headers)
12. Add a GitHub Actions workflow running `composer test` on push/PR

---
Changelog:
- 2.4.0: Full HTTP verb support added (PUT, PATCH, DELETE, OPTIONS, HEAD, match, any), route group middleware inheritance, Request method spoofing (`_method`, `X-HTTP-Method-Override`), and Request inspection helpers (`header()`, `isMethod()`, `isJson()`, `expectsJson()`) with 36 passing Pest tests / 106 assertions.
- 2.3.0: Added `abort()` helper in `src/Support/helpers.php`, added `getBody()` / `getHeader()` to `Response`, added `filp/whoops` to `require-dev`, and wrote Pest test suites for `Handler`, `HttpException`, `Helper`, and `Middleware` (total 26 tests / 63 assertions passing).
- 2.2.0: Exception handling shipped (Handler + HttpException), LogMiddleware added, .env.example + phpunit.xml committed, first Pest suite passing (10/10).
- 2.1.1: Added scaffold details, clarified version-in-file strategy
- 2.0.0: Lowercase repo decision
- 1.0.0: Initial
