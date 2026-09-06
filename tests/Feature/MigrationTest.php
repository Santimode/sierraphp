<?php

use Sierra\Database\Connection;
use Sierra\Application;
use Sierra\Console\Commands\MigrateCommand;

beforeEach(function () {
    $this->db = new Connection([
        'driver' => 'sqlite',
        'database' => ':memory:',
    ]);
    
    $this->app = new Application(sys_get_temp_dir() . '/sierra_test');
    $this->app->getContainer()->instance(Connection::class, $this->db);
    
    if (!is_dir($this->app->getBasePath() . '/database/migrations')) {
        @mkdir($this->app->getBasePath() . '/database/migrations', 0777, true);
    }
});

afterEach(function () {
    $files = glob($this->app->getBasePath() . '/database/migrations/*.php');
    if ($files) {
        array_map('unlink', $files);
    }
});

test('it runs migrations and records them', function () {
    $migrationPath = $this->app->getBasePath() . '/database/migrations/2026_09_07_000000_create_test_table.php';
    file_put_contents($migrationPath, "<?php
    class CreateTestTable {
        public function up(\$db) {
            \$db->statement('CREATE TABLE test_table (id INTEGER PRIMARY KEY)');
        }
    }
    ");
    
    $command = new MigrateCommand($this->app);
    
    ob_start();
    $status = $command->handle();
    $output = ob_get_clean();
    
    expect($status)->toBe(0);
    expect($output)->toContain('Migrating:');
    
    $tables = $this->db->select("SELECT name FROM sqlite_master WHERE type='table' AND name='test_table'");
    expect($tables)->toHaveCount(1);
    
    $migrations = $this->db->table('migrations')->get();
    expect($migrations)->toHaveCount(1);
    expect($migrations[0]->migration)->toBe('2026_09_07_000000_create_test_table.php');
});

test('it does not run already run migrations', function () {
    $command = new MigrateCommand($this->app);
    
    $reflection = new ReflectionClass($command);
    $method = $reflection->getMethod('ensureMigrationsTableExists');
    $method->setAccessible(true);
    $method->invoke($command);
    
    $this->db->table('migrations')->insert([
        'migration' => '2026_09_07_000000_create_test_table.php',
        'batch' => 1
    ]);
    
    $migrationPath = $this->app->getBasePath() . '/database/migrations/2026_09_07_000000_create_test_table.php';
    file_put_contents($migrationPath, "<?php
    class CreateTestTable {
        public function up(\$db) {
            // this should not run
        }
    }
    ");
    
    ob_start();
    $status = $command->handle();
    $output = ob_get_clean();
    
    expect($status)->toBe(0);
    expect($output)->toContain('Nothing to migrate.');
});
