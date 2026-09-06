<?php
declare(strict_types=1);

namespace Sierra\Console;

use Sierra\Application;

class Kernel
{
    public function __construct(
        protected Application $app
    ) {}

    public function handle(array $argv): int
    {
        $command = $argv[1] ?? 'help';

        switch ($command) {
            case 'serve':
                return $this->serve();
            case 'route:clear':
                return $this->routeClear();
            case 'migrate':
                return (new \Sierra\Console\Commands\MigrateCommand($this->app))->handle();
            case 'help':
            default:
                return $this->help();
        }
    }

    protected function serve(): int
    {
        $host = 'localhost';
        $port = '8000';
        $publicDir = $this->app->getBasePath() . '/public';

        echo "Starting sierraPHP development server at http://{$host}:{$port}\n";
        
        $command = escapeshellcmd("php -S {$host}:{$port} -t {$publicDir}");
        passthru($command, $status);
        
        return $status;
    }

    protected function routeClear(): int
    {
        $cacheFile = $this->app->getBasePath() . '/storage/cache/routes.cache';
        if (file_exists($cacheFile)) {
            unlink($cacheFile);
            echo "Route cache cleared successfully.\n";
        } else {
            echo "No route cache found.\n";
        }
        
        return 0;
    }

    protected function help(): int
    {
        echo "sierraPHP CLI\n\n";
        echo "Usage:\n";
        echo "  sierra [command]\n\n";
        echo "Available commands:\n";
        echo "  serve         Start the development server\n";
        echo "  route:clear   Clear the route cache\n";
        echo "  migrate       Run database migrations\n";
        echo "  help          Show this help message\n";

        return 0;
    }
}
