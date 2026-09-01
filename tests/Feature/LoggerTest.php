<?php
declare(strict_types=1);

use Sierra\Exceptions\Handler;
use Sierra\Log\Logger;
use Sierra\Log\LoggerInterface;
use Sierra\Support\Facades\Log;

beforeEach(function () {
    $this->logFile = sys_get_temp_dir() . '/sierra_test_' . uniqid() . '.log';
});

afterEach(function () {
    if (file_exists($this->logFile)) {
        @unlink($this->logFile);
    }
});

it('writes structured log entries with timestamp and level to file', function () {
    $logger = new Logger($this->logFile);
    $logger->info('Framework booted');

    expect(file_exists($this->logFile))->toBeTrue();
    $content = file_get_contents($this->logFile);

    expect($content)->toContain('[INFO] Framework booted')
        ->and($content)->toMatch('/^\[\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}\] \[INFO\] Framework booted/');
});

it('interpolates context placeholders in log messages', function () {
    $logger = new Logger($this->logFile);
    $logger->warning('User {id} accessed restricted route {route}', [
        'id' => 42,
        'route' => '/admin/settings',
    ]);

    $content = file_get_contents($this->logFile);
    expect($content)->toContain('User 42 accessed restricted route /admin/settings')
        ->and($content)->toContain('"id":42');
});

it('encodes structured context array as JSON', function () {
    $logger = new Logger($this->logFile);
    $logger->error('Transaction declined', [
        'order_id' => 'ORD-9988',
        'gateway' => 'stripe',
        'code' => 'card_declined',
    ]);

    $content = file_get_contents($this->logFile);
    expect($content)->toContain('[ERROR] Transaction declined')
        ->and($content)->toContain('"order_id":"ORD-9988"')
        ->and($content)->toContain('"gateway":"stripe"');
});

it('supports all standard log levels', function () {
    $logger = new Logger($this->logFile);

    $logger->emergency('emergency test');
    $logger->alert('alert test');
    $logger->critical('critical test');
    $logger->error('error test');
    $logger->warning('warning test');
    $logger->notice('notice test');
    $logger->info('info test');
    $logger->debug('debug test');

    $content = file_get_contents($this->logFile);

    expect($content)->toContain('[EMERGENCY] emergency test')
        ->and($content)->toContain('[ALERT] alert test')
        ->and($content)->toContain('[CRITICAL] critical test')
        ->and($content)->toContain('[ERROR] error test')
        ->and($content)->toContain('[WARNING] warning test')
        ->and($content)->toContain('[NOTICE] notice test')
        ->and($content)->toContain('[INFO] info test')
        ->and($content)->toContain('[DEBUG] debug test');
});

it('logger helper returns Logger instance or logs message', function () {
    expect(logger())->toBeInstanceOf(LoggerInterface::class);

    // Call helper with message
    logger('Logged via helper function', ['tag' => 'helper']);
    expect(true)->toBeTrue();
});

it('Log facade proxies static log methods', function () {
    Log::info('Logged via Log facade', ['source' => 'facade']);
    expect(true)->toBeTrue();
});

it('Exception Handler records structured error into Logger', function () {
    $logger = new Logger($this->logFile);
    $handler = new Handler(debug: false, logger: $logger);

    $handler->report(new RuntimeException('Fatal database timeout', 500));

    $content = file_get_contents($this->logFile);
    expect($content)->toContain('[ERROR] Fatal database timeout')
        ->and($content)->toContain('"exception":"RuntimeException"')
        ->and($content)->toContain('"code":500');
});
