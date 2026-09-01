<?php
declare(strict_types=1);
namespace Sierra\Exceptions;

use Sierra\Http\Response;
use Throwable;

final class Handler
{
    public function __construct(private bool $debug = true) {}

    public function handle(Throwable $e): Response
    {
        $this->report($e);

        if ($this->debug) {
            return $this->renderDebug($e);
        }

        return $this->renderProduction($e);
    }

    public function report(Throwable $e): void
    {
        error_log($e->getMessage() . " in " . $e->getFile() . ":" . $e->getLine());
    }

    private function renderDebug(Throwable $e): Response
    {
        $status = $e instanceof \Sierra\Http\HttpException ? $e->getStatusCode() : 500;

        // Use Whoops if available
        if (class_exists(\Whoops\Run::class)) {
            $whoops = new \Whoops\Run;
            $whoops->allowQuit(false);
            $whoops->writeToOutput(false);
            $handler = new \Whoops\Handler\PrettyPageHandler();
            $handler->handleUnconditionally(true);
            $whoops->pushHandler($handler);
            $html = $whoops->handleException($e);
            return new Response($html, $status, ['Content-Type' => 'text/html']);
        }

        // Fallback pretty page
        $html = sprintf(
            '<html><head><title>Error - sierraPHP</title><style>body{font-family:system-ui;background:#0f0f0f;color:#fff;padding:40px}pre{background:#1a1a1a;padding:20px;border-radius:8px;overflow:auto;border:1px solid #333}h1{color:#f87171}code{color:#7dd3fc}</style></head><body><h1>⛰️ sierraPHP Error</h1><p><code>%s</code> in <code>%s:%d</code></p><pre>%s</pre><pre>%s</pre></body></html>',
            htmlspecialchars($e->getMessage()),
            htmlspecialchars($e->getFile()),
            $e->getLine(),
            htmlspecialchars($e->getTraceAsString()),
            htmlspecialchars((string)$e)
        );
        return new Response($html, $status, ['Content-Type' => 'text/html']);
    }

    private function renderProduction(Throwable $e): Response
    {
        $status = $e instanceof \Sierra\Http\HttpException ? $e->getStatusCode() : 500;
        return (new Response())->json([
            'message' => 'Server Error',
            'framework' => 'sierraPHP',
        ], $status);
    }
}
