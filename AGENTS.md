# AGENTS.md — sierraPHP (sierraphp)

> Version: 2.4.0 | Repo: Santimode/sierraphp | Updated: 2026-09-01
> Brand: sierraPHP | This file instructs AI coding agents.

### Naming
- Repo/Composer/Folder: `sierraphp` lowercase — NEVER `sierraPHP` in technical paths
- Brand: `sierraPHP` for display only
- Namespace: `Sierra\`
- Filenames: ALWAYS `README.md`, `AIHANDOFF.md`, `AGENTS.md` — no V2, V3 suffix. Version goes inside file header.

### Who You Are
Senior PHP framework contributor. Build framework code, not app code.

### Commands
```bash
composer install
composer test
php -S localhost:8000 -t public
```

### Style
- declare(strict_types=1); every file
- PSR-12, final class for impl, interface for contract
- No $_GET/$_POST outside Http\Request
- No facades inside src/ core
- No illuminate/* packages

### Architecture Rules
1. Container is king — no `new` outside container except in Application
2. Router returns Route + params, does not execute
3. Request immutable-ish via withAttribute()
4. Response fluent
5. Middleware: process(Request $req, callable $next): Response

### File Ownership
- Container: no deps on Router/Http
- Router: only Container
- Http: standalone (includes HttpException — carries a status code, no framework deps)
- Exceptions: depends only on Http (needs Response, checks for HttpException); catches Throwable in Application::run(), never lets an exception escape to the client uncaught
- Application: wires all, owns the try/catch boundary around dispatch
- Middleware: LogMiddleware is standalone, depends only on Http
- Support: helpers + facades

### Versioning Inside File (per user request)
Do NOT create README-V2.md. Instead:
- Keep filename clean
- Put at top: `> Version: X.Y.Z | Updated: YYYY-MM-DD`
- Keep changelog at bottom

### Good / Bad
Good: Container auto-wiring + test
Good: Exception Handler with distinct debug/production render paths, status code preserved via HttpException
Good: Writing matching Pest tests for all core src/ components (Container, Request, Router, HttpException, Handler, Helper, Middleware)
Bad: Adds ORM, Auth, Blade in MVP, or uppercase composer name
Bad: Leaking stack traces or file paths to the client when APP_DEBUG=false
Bad: Writing new src/ code without a matching Pest test

---
Changelog:
- 2.4.0: Added full HTTP verbs (PUT, PATCH, DELETE, OPTIONS, HEAD, match, any), route group middleware inheritance, method spoofing, and request inspection helpers
- 2.3.0: Full Pest test suite for Handler, HttpException, Helper, Middleware; abort() helper added
- 2.2.0: Added Exceptions\Handler + Http\HttpException, LogMiddleware, file ownership rule for Exceptions module
- 2.1.1: Enforced clean filenames, version inside file
- 2.0.0: Lowercase repo
- 1.0.0: Initial
