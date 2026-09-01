# sierraPHP

> Light as the Sierra Madre, powerful as it needs to be.

A minimalist PHP framework inspired by **Slim's speed** and **Laravel's elegance**.

**Version:** 2.2.0  
**Repo:** `Santimode/sierraphp` — https://github.com/Santimode/sierraphp  
**Package:** `santimode/sierraphp`  
**Last Updated:** 2026-09-01  
**Status:** MVP Scaffolded

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

### Project Structure
```
sierraphp/
├── src/
│   ├── Application.php
│   ├── Container/Container.php
│   ├── Router/
│   ├── Http/
│   └── Support/
├── public/index.php
├── routes/web.php
├── config/app.php
└── composer.json
```

### Naming
- Repo/Composer: `santimode/sierraphp` (lowercase)
- Brand: `sierraPHP`
- Namespace: `Sierra\`

### Changelog (inside file versioning)
- 2.2.0 (2026-09-01): Initial MVP scaffold — Container, Router, Request/Response, Middleware Stack
- 2.0.0 (2026-09-01): Docs updated to lowercase repo
- 1.0.0 (2026-09-01): Initial docs

### License
MIT © Santimode

- 2.2.0 (2026-09-02): Handler + Whoops + Pest tests + LogMiddleware
