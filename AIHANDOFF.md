# AIHANDOFF.md — sierraPHP

> Version: 2.6.0 | Repo: Santimode/sierraphp | Package: santimode/sierraphp | Updated: 2026-09-01
> Brand: sierraPHP | Namespace: Sierra\ | PHP: ^8.2

This file is the single source of truth for AI agents.

### 1. Snapshot
- Repo: https://github.com/Santimode/sierraphp (public, active — MVP scaffold + full HTTP verbs & router completeness + Error Content Negotiation + Security Middleware + Structured File Logging + GitHub Actions CI done)
- Current state: Container, Router, Http, Middleware, Exceptions\Handler (with dual-mode content negotiation), Log (LoggerInterface, Logger, Log facade, logger() helper), and CI workflow all implemented and thoroughly tested. `composer test` passes (49 tests, 158 assertions).
- Goal: Lightweight, ultra-fast PHP micro-framework ready for production workloads.

### 2. Naming — FINAL
- Filesystem: `sierraphp` lowercase everywhere
- Display: `sierraPHP`
- Composer: `santimode/sierraphp`
- Do NOT use V2 in filename. Version lives in file header (this header).

### 3. MVP Done Criteria
```php
Route::group(['prefix' => '/api', 'middleware' => [CorsMiddleware::class, SecurityHeadersMiddleware::class, LogMiddleware::class]], function($router) {
    $router->put('/items/{id}', function(Request $req, string $id) {
        logger('Updating item {id}', ['id' => $id]);
        return response()->json(['updated' => $id]);
    });
});
```
Tests: full HTTP verbs (GET, POST, PUT, PATCH, DELETE, OPTIONS, HEAD, match, any) ✅, method spoofing ✅, group middleware & prefixes ✅, CorsMiddleware & SecurityHeadersMiddleware ✅, error content negotiation (JSON/HTML in debug & production) ✅, structured file logging & placeholder interpolation ✅, container auto-wire & optional injection ✅, json response ✅, abort() helper ✅ (49 tests / 158 assertions)

### 4. Architecture Decisions
- fast-route for dispatch, wrapped in Sierra\Router\Router
- Simple PSR-11 container with auto-wiring
- Minimal Request/Response (not full PSR-7, but compatible ideas)
- Middleware Stack is PSR-15-like
- Application is glue

### 5. File Map (v2.6.0 Scaffold)
- src/Application.php — creates container, router, logger, runs dispatch; forwards all router verbs, wraps dispatch in try/catch, delegates to Exceptions\Handler on Throwable with Request context
- src/Container/Container.php — bind/singleton/instance/get/has/make (with auto-wiring and optional default value fallback)
- src/Router/Route.php — value object
- src/Router/Router.php — get/post/put/patch/delete/options/head/match/any/group with middleware and prefix support
- src/Http/Request.php — from globals with method spoofing, header(), isMethod(), isJson(), expectsJson(), input(), query(), param bag
- src/Http/Response.php — json(), view(), send(), getStatusCode(), getBody(), getHeader(), getHeaders()
- src/Http/HttpException.php — RuntimeException + statusCode, for throwing intentional HTTP errors from route/controller code
- src/Exceptions/Handler.php — report() with structured logger, renderDebugJson/Html, renderProductionJson/Html (content negotiated)
- src/Log/* — LoggerInterface, Logger (structured file logging to storage/logs/sierra.log with context JSON & message interpolation)
- src/Middleware/* — MiddlewareInterface, Stack, LogMiddleware, CorsMiddleware, SecurityHeadersMiddleware
- src/Support/helpers.php — abort(), app(), response(), request(), view(), config(), env(), logger()
- src/Support/Facades/{Route.php, Log.php} — static facades
- .github/workflows/tests.yml — GitHub Actions CI matrix on PHP 8.2, 8.3, 8.4
- public/index.php — front controller
- routes/web.php — example routes
- tests/Feature/{ContainerTest,RequestTest,RouterTest,HttpExceptionTest,HandlerTest,HelperTest,MiddlewareTest,LoggerTest}.php — Pest, 49 tests / 158 assertions
- .env.example, phpunit.xml — required for a clean fresh-clone setup, committed

### 6. Versioning Inside File (your request)
Instead of `README-V2.md`, use:
```
Version: 2.6.0
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
9. ✅ Error Content Negotiation (`Accept: application/json` vs HTML in debug and production modes)
10. ✅ Security Middleware (`CorsMiddleware`, `SecurityHeadersMiddleware`)
11. ✅ GitHub Actions automated CI matrix workflow across PHP 8.2, 8.3, 8.4
12. ✅ Structured File Logging (`Sierra\Log\Logger`, `Log` facade, `logger()` helper, structured `Handler` & `LogMiddleware` integration)

---
Changelog:
- 2.6.0: Added Structured File Logging (`Sierra\Log\LoggerInterface`, `Sierra\Log\Logger`, `Sierra\Support\Facades\Log`, `logger()` helper), integrated structured logging into `Exceptions\Handler` and `LogMiddleware`, and added comprehensive Pest tests (49 passing tests / 158 assertions).
- 2.5.0: Added Error Content Negotiation (structured JSON vs pretty HTML in both debug and production modes), built-in security middleware (`CorsMiddleware`, `SecurityHeadersMiddleware`), and GitHub Actions CI workflow for PHP 8.2, 8.3, 8.4.
- 2.4.0: Full HTTP verb support added (PUT, PATCH, DELETE, OPTIONS, HEAD, match, any), route group middleware inheritance, Request method spoofing (`_method`, `X-HTTP-Method-Override`), and Request inspection helpers.
- 2.3.0: Added `abort()` helper in `src/Support/helpers.php`, added `getBody()` / `getHeader()` to `Response`, added `filp/whoops` to `require-dev`, and wrote Pest test suites for `Handler`, `HttpException`, `Helper`, and `Middleware`.
- 2.2.0: Exception handling shipped (Handler + HttpException), LogMiddleware added, .env.example + phpunit.xml committed, first Pest suite passing (10/10).
- 2.1.1: Added scaffold details, clarified version-in-file strategy
- 2.0.0: Lowercase repo decision
- 1.0.0: Initial
