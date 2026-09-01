# AIHANDOFF.md — sierraPHP

> Version: 2.2.0 | Repo: Santimode/sierraphp | Package: santimode/sierraphp | Updated: 2026-09-01
> Brand: sierraPHP | Namespace: Sierra\ | PHP: ^8.2

This file is the single source of truth for AI agents.

### 1. Snapshot
- Repo created: https://github.com/Santimode/sierraphp (public, empty)
- Current local state: VS Code shows Sierraphp folder with -V2.md files (needs rename to clean names)
- Goal: Build MVP framework — router + container + http + middleware — in < 50ms

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
Tests: router param, middleware chain, container auto-wire, json response, 404

### 4. Architecture Decisions
- fast-route for dispatch, wrapped in Sierra\Router\Router
- Simple PSR-11 container with auto-wiring
- Minimal Request/Response (not full PSR-7, but compatible ideas)
- Middleware Stack is PSR-15-like
- Application is glue

### 5. File Map (v2.2.0 Scaffold)
- src/Application.php — creates container, router, runs dispatch
- src/Container/Container.php — bind/singleton/get/has/make
- src/Router/Route.php — value object
- src/Router/Router.php — collection + group + fast-route integration
- src/Http/Request.php — from globals, input(), query(), param bag
- src/Http/Response.php — json(), view(), send()
- src/Middleware/* — interface + stack
- src/Support/helpers.php — app(), response(), request(), view(), config(), env()
- public/index.php — front controller
- routes/web.php — example routes

### 6. Versioning Inside File (your request)
Instead of `README-V2.md`, use:
```
Version: 2.2.0
Last Updated: 2026-09-01
Changelog at bottom of file
```
Clean filenames: README.md, AIHANDOFF.md, AGENTS.md

### 7. Next Steps After Scaffold
1. composer install
2. php -S localhost:8000 -t public
3. Implement Exceptions/Handler for pretty errors
4. Add Pest tests

---
Changelog:
- 2.2.0: Added scaffold details, clarified version-in-file strategy
- 2.0.0: Lowercase repo decision
- 1.0.0: Initial
