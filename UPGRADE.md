# Upgrading To sierraPHP 2.8.0

This guide covers upgrading your sierraPHP application from older versions (1.x or early 2.x) to the latest **2.8.0** release. 

> [!CAUTION]
> Before beginning any upgrade process, ensure you have backed up your application and committed all changes to version control.

---

## 1. Verifying Server Environments

sierraPHP takes advantage of modern PHP features to remain as lightweight and fast as possible. 

- **PHP Version**: Ensure your server is running **PHP 8.2** or higher.
- **Directory Permissions**: The framework now writes to the filesystem for logging and route caching. Ensure the web server user has write permissions to:
  - `storage/logs/` (for structured logging)
  - `storage/cache/` (for production route caching)

If these directories do not exist, create them in your project root:
```bash
mkdir -p storage/logs storage/cache
chmod -R 755 storage
```

---

## 2. Adjusting Composer Dependencies

Update your `composer.json` to reflect the latest package versions and development requirements.

1. Update the `santimode/sierraphp` version constraint (if you are consuming it as a package):
   ```json
   "require": {
       "php": "^8.2",
       "santimode/sierraphp": "^2.7"
   }
   ```
2. If you maintain the core structure directly, ensure your core dependencies are up-to-date:
   ```json
   "require": {
       "php": "^8.2",
       "nikic/fast-route": "^1.3",
       "vlucas/phpdotenv": "^5.6"
   }
   ```
3. Add the new development dependencies for robust error handling and testing:
   ```json
   "require-dev": {
       "filp/whoops": "^2.15",
       "pestphp/pest": "^2.0"
   }
   ```
4. Run `composer update` to pull in the fresh dependencies.

---

## 3. Core Class and Structural Updates

Depending on how old your version is, several core architectural components have been introduced.

### Application Bootstrapping (`src/Application.php`)
In `2.7.0`, production route caching was introduced. You must update your `Application` constructor to pass cache configurations to the Router.

Open `src/Application.php` and ensure the end of your constructor looks like this:

```php
$debug = (bool)($this->config['debug'] ?? env('APP_DEBUG', true));
$this->exceptionHandler = new Handler($debug, $this->logger);
$this->container->instance(Handler::class, $this->exceptionHandler);

// ADDED IN 2.7.0: Route Caching
$routeCacheEnabled = !$debug;
$routeCacheFile = $this->basePath . '/storage/cache/routes.cache';
if (!is_dir(dirname($routeCacheFile))) {
    @mkdir(dirname($routeCacheFile), 0755, true);
}
$this->router->setCacheConfig($routeCacheEnabled, $routeCacheFile);
```

> [!TIP]
> This change ensures that when `APP_DEBUG=false`, FastRoute will cache your compiled regexes, drastically improving performance.

### Middleware Integrations
Versions `2.5.0` and above include `CorsMiddleware`, `SecurityHeadersMiddleware`, and `LogMiddleware`. You should update your route groups to utilize them.

In `routes/web.php`:
```diff
- Route::group('/api', function($router) {
+ Route::group(['prefix' => '/api', 'middleware' => [
+     \Sierra\Middleware\CorsMiddleware::class,
+     \Sierra\Middleware\SecurityHeadersMiddleware::class, 
+     \Sierra\Middleware\LogMiddleware::class
+ ]], function($router) {
      // API routes...
  });
```

### New Helper Functions
A new `route()` helper was introduced in `2.7.0` for generating named route URLs. Ensure your `src/Support/helpers.php` contains the following block at the bottom:

```php
if (!function_exists('route')) {
    function route(string $name, array $params = []): string
    {
        global $sierraApp;
        if (!$sierraApp) throw new \RuntimeException("Application not booted");
        return $sierraApp->getRouter()->generateUrl($name, $params);
    }
}
```

### Database & Validation (New in 2.8.0)
Version `2.8.0` introduces a lightweight PDO wrapper and a robust validation component.

To use the new Database component, you must update your `.env` file to include database credentials:
```env
DB_CONNECTION=sqlite
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=C:\path\to\your\project\database\database.sqlite
DB_USERNAME=root
DB_PASSWORD=
```

You must also update your `src/Application.php` to bind the Database connection and create the `database` folder.
Ensure the following snippet is in your `Application` constructor:
```php
        $dbConfig = [
            'driver' => env('DB_CONNECTION', 'sqlite'),
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '3306'),
            'database' => env('DB_DATABASE', $this->basePath . '/database/database.sqlite'),
            'username' => env('DB_USERNAME', 'root'),
            'password' => env('DB_PASSWORD', ''),
        ];
        
        $this->container->singleton(\Sierra\Database\Connection::class, function () use ($dbConfig) {
            return new \Sierra\Database\Connection($dbConfig);
        });
```

### CLI Tool & Migrations (New in 2.8.0)
Version `2.8.0` also introduces a unified CLI entry point. Create the `bin/sierra` executable and the `src/Console/Kernel.php` to access `serve`, `route:clear`, and the new `migrate` commands. Ensure `bin/sierra` is executable (`chmod +x bin/sierra`).

---

## 4. Testing the Upgrade

Once your files are updated, flush any existing caches and test the application:

1. Clear the old route cache (if any) by running `rm -f storage/cache/routes.cache`.
2. Start the local server: `php -S localhost:8000 -t public`
3. Hit your endpoints in both `APP_DEBUG=true` (to ensure the Whoops handler triggers on exceptions) and `APP_DEBUG=false` (to ensure `routes.cache` generates successfully).
4. Run your test suite using `composer test` to ensure stability.
