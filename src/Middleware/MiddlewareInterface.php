<?php
declare(strict_types=1);
namespace Sierra\Middleware;

use Sierra\Http\Request;
use Sierra\Http\Response;

interface MiddlewareInterface
{
    public function process(Request $request, callable $next): Response;
}
