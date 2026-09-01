<?php
declare(strict_types=1);
namespace Sierra\Exceptions;

use Sierra\Http\Request;
use Sierra\Http\Response;
use Sierra\Http\HttpException;
use Sierra\Log\LoggerInterface;
use Throwable;

final class Handler
{
    public function __construct(
        private bool $debug = true,
        private ?LoggerInterface $logger = null
    ) {}

    public function handle(Throwable $e, ?Request $request = null): Response
    {
        $this->report($e);

        $request = $request ?? (function_exists('request') ? request() : null);
        if (!($request instanceof Request)) {
            $request = null;
        }

        $wantsJson = $request?->expectsJson() ?? false;

        if ($this->debug) {
            return $wantsJson ? $this->renderDebugJson($e) : $this->renderDebugHtml($e);
        }

        return $wantsJson ? $this->renderProductionJson($e) : $this->renderProductionHtml($e);
    }

    public function report(Throwable $e): void
    {
        if ($this->logger) {
            $this->logger->error($e->getMessage(), [
                'exception' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'code' => $e->getCode(),
            ]);
        } else {
            error_log($e->getMessage() . " in " . $e->getFile() . ":" . $e->getLine());
        }
    }

    private function renderDebugJson(Throwable $e): Response
    {
        $status = $e instanceof HttpException ? $e->getStatusCode() : 500;

        return (new Response())->json([
            'error' => [
                'message' => $e->getMessage(),
                'exception' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => explode("\n", $e->getTraceAsString()),
            ],
            'framework' => 'sierraPHP',
        ], $status);
    }

    private function renderDebugHtml(Throwable $e): Response
    {
        $status = $e instanceof HttpException ? $e->getStatusCode() : 500;

        // Use Whoops if available
        if (class_exists(\Whoops\Run::class)) {
            $whoops = new \Whoops\Run();
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

    private function getStatusMessage(int $status): string
    {
        return match ($status) {
            400 => 'Bad Request',
            401 => 'Unauthorized',
            403 => 'Forbidden',
            404 => 'Not Found',
            405 => 'Method Not Allowed',
            422 => 'Unprocessable Entity',
            429 => 'Too Many Requests',
            503 => 'Service Unavailable',
            default => 'Server Error',
        };
    }

    private function renderProductionJson(Throwable $e): Response
    {
        $status = $e instanceof HttpException ? $e->getStatusCode() : 500;
        $message = $e instanceof HttpException && $e->getMessage() !== ''
            ? $e->getMessage()
            : $this->getStatusMessage($status);

        return (new Response())->json([
            'message' => $message,
            'framework' => 'sierraPHP',
        ], $status);
    }

    private function renderProductionHtml(Throwable $e): Response
    {
        $status = $e instanceof HttpException ? $e->getStatusCode() : 500;
        $message = $e instanceof HttpException && $e->getMessage() !== ''
            ? $e->getMessage()
            : $this->getStatusMessage($status);

        $html = sprintf(
            '<!DOCTYPE html><html><head><meta charset="utf-8"><title>%d %s - sierraPHP</title><style>body{margin:0;padding:0;background:#0b0f19;color:#f3f4f6;font-family:system-ui,-apple-system,sans-serif;display:flex;align-items:center;justify-content:center;min-height:100vh}div{text-align:center;padding:2rem}h1{font-size:4rem;margin:0;color:#ef4444;font-weight:700}p{font-size:1.25rem;color:#9ca3af;margin:1rem 0 0}</style></head><body><div><h1>%d</h1><p>%s</p></div></body></html>',
            $status,
            htmlspecialchars($message),
            $status,
            htmlspecialchars($message)
        );

        return new Response($html, $status, ['Content-Type' => 'text/html']);
    }
}
