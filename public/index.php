<?php
declare(strict_types=1);
require __DIR__ . '/../vendor/autoload.php';

$app = Sierra\Application::create(__DIR__ . '/..');

// Load routes
require __DIR__ . '/../routes/web.php';

$app->run();
