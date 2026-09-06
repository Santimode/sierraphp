<?php
declare(strict_types=1);
namespace Sierra\Console\Commands;

use Sierra\Database\Connection;
use Sierra\Application;

class MigrateCommand
{
    protected Application $app;
    protected Connection $db;

    public function __construct(Application $app)
    {
        $this->app = $app;
        $this->db = $app->getContainer()->get(Connection::class);
    }

    public function handle(): int
    {
        $this->ensureMigrationsTableExists();

        $migrationsPath = $this->app->getBasePath() . '/database/migrations';
        if (!is_dir($migrationsPath)) {
            echo "Migrations directory not found at {$migrationsPath}\n";
            return 1;
        }

        $files = glob($migrationsPath . '/*.php');
        if (empty($files)) {
            echo "Nothing to migrate.\n";
            return 0;
        }

        $ranMigrations = $this->getRanMigrations();
        $migrated = false;

        foreach ($files as $file) {
            $filename = basename($file);
            if (in_array($filename, $ranMigrations)) {
                continue;
            }

            require_once $file;
            $class = $this->getMigrationClassName($filename);

            if (class_exists($class)) {
                $migration = new $class();
                if (method_exists($migration, 'up')) {
                    echo "Migrating: {$filename}\n";
                    $migration->up($this->db);
                    $this->recordMigration($filename);
                    echo "Migrated:  {$filename}\n";
                    $migrated = true;
                }
            }
        }

        if (!$migrated) {
            echo "Nothing to migrate.\n";
        }

        return 0;
    }

    protected function ensureMigrationsTableExists(): void
    {
        $driver = $this->db->getPdo()->getAttribute(\PDO::ATTR_DRIVER_NAME);
        
        if ($driver === 'sqlite') {
            $sql = "
                CREATE TABLE IF NOT EXISTS migrations (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    migration VARCHAR(255) NOT NULL,
                    batch INTEGER NOT NULL DEFAULT 1
                )
            ";
        } else {
            $sql = "
                CREATE TABLE IF NOT EXISTS migrations (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    migration VARCHAR(255) NOT NULL,
                    batch INT NOT NULL DEFAULT 1
                )
            ";
        }

        $this->db->statement($sql);
    }

    protected function getRanMigrations(): array
    {
        $results = $this->db->select("SELECT migration FROM migrations");
        return array_map(fn($row) => $row->migration ?? $row['migration'], $results);
    }

    protected function recordMigration(string $filename): void
    {
        $this->db->insert("INSERT INTO migrations (migration, batch) VALUES (?, ?)", [$filename, 1]);
    }

    protected function getMigrationClassName(string $filename): string
    {
        $name = str_replace('.php', '', $filename);
        $name = preg_replace('/^\d{4}_\d{2}_\d{2}_\d{6}_/', '', $name);
        return str_replace(' ', '', ucwords(str_replace('_', ' ', $name)));
    }
}
