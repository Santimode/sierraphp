# sierraPHP

> Light as the Sierra Madre, powerful as it needs to be.

A minimalist PHP framework inspired by **Slim's speed** and **Laravel's elegance**.

**Version:** 2.5.0  
**Repo:** `Santimode/sierraphp` — https://github.com/Santimode/sierraphp  
**Package:** `santimode/sierraphp`  
**Last Updated:** 2026-09-01  
**Status:** MVP scaffolded & tested, core hardening in progress — not production-ready yet (see Known Gaps)

---

### Philosophy
1. Light by default: < 20 core files
2. Slim-inspired: FastRoute, PSR-15 middleware, front controller
3. Laravel-inspired: `Route::get()`, `Route::post()`, `Route::put()`, `Route::delete()`, `app()`, `abort()`, `response()->json()`, auto-wiring container
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

Route::group(['prefix' => '/api', 'middleware' => [CorsMiddleware::class, SecurityHeadersMiddleware::class, LogMiddleware::class]], function($router) {
    $router->get('/users/{id}', function(Request $req, string $id) {
        if ($id === '404') abort(404, 'User not found');
        return response()->json(['user' => $id]);
    });
    
    $router->put('/users/{id}', fn(Request $req, string $id) => response()->json(['updated' => $id, 'data' => $req->all()]));
    $router->delete('/users/{id}', fn(Request $req, string $id) => response()->json(['deleted' => $id]));
});
```

### Testing
```bash
composer test
```
Runs the Pest suite (`ContainerTest`, `RequestTest`, `RouterTest`, `HttpExceptionTest`, `HandlerTest`, `HelperTest`, `MiddlewareTest` — 41 tests, 132 assertions as of 2.5.0). Automated GitHub Actions CI workflow runs tests against PHP 8.2, 8.3, and 8.4 on every push and PR.

### Error Handling & Content Negotiation
Uncaught exceptions and errors are caught centrally in `Application::run()` and passed to `Sierra\Exceptions\Handler` with full client request format negotiation:
- **Debug mode** (`APP_DEBUG=true`):
  - **API Requests** (`Accept: application/json` or `expectsJson()`): Returns structured JSON with exception class, message, file, line, trace array, and preserved status code.
  - **Browser Requests** (`text/html`): Renders a rich error page via Whoops if installed, otherwise a built-in dark-themed HTML fallback.
- **Production mode** (`APP_DEBUG=false`):
  - **API Requests**: Returns generic JSON error response (`Server Error` or `HttpException` message), masking internal code details.
  - **Browser Requests**: Renders a clean, styled HTTP error screen.

### Project Structure
```
sierraphp/
├── .github/workflows/tests.yml
├── src/
│   ├── Application.php
│   ├── Container/Container.php
│   ├── Router/
│   │   ├── Route.php
│   │   └── Router.php
│   ├── Http/
│   │   ├── Request.php
│   │   ├── Response.php
│   │   └── HttpException.php
│   ├── Exceptions/Handler.php
│   ├── Middleware/
│   │   ├── MiddlewareInterface.php
│   │   ├── Stack.php
│   │   ├── LogMiddleware.php
│   │   ├── CorsMiddleware.php
│   │   └── SecurityHeadersMiddleware.php
│   └── Support/
│       ├── helpers.php
│       └── Facades/Route.php
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
- Logging is `error_log()` only, no swappable/structured logger

### Changelog (inside file versioning)
- 2.5.0 (2026-09-01): Added Error Content Negotiation (structured JSON vs pretty HTML in debug and production modes), security middleware (`CorsMiddleware`, `SecurityHeadersMiddleware`), and GitHub Actions CI workflow for PHP 8.2, 8.3, 8.4 (41 passing tests / 132 assertions)
- 2.4.0 (2026-09-01): Added full HTTP verb routing (PUT, PATCH, DELETE, OPTIONS, HEAD, match, any), route group middleware inheritance, Request method spoofing (`_method`, `X-HTTP-Method-Override`), and Request inspection helpers (36 passing tests / 106 assertions)
- 2.3.0 (2026-09-01): Added `abort()` helper, `Response::getBody()` / `Response::getHeader()`, `filp/whoops` require-dev, and full Pest test coverage across `Handler`, `HttpException`, `Helper`, and `Middleware` (26 passing tests / 63 assertions)
- 2.2.0 (2026-09-01): Added `Exceptions\Handler` + `Http\HttpException` (wired into `Application::run()`), `LogMiddleware`, `.env.example`, `phpunit.xml`, and the first Pest tests (Container, Request, Router — 10 passing)
- 2.1.1 (2026-09-01): Cleaned duplicate code, fixed `Request` immutability
- 2.1.0 (2026-09-01): Initial MVP scaffold — Container, Router, Request/Response, Middleware Stack
- 2.0.0 (2026-09-01): Docs updated to lowercase repo
- 1.0.0 (2026-09-01): Initial docs

### License
MIT © Santimode
