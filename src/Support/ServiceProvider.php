<?php
declare(strict_types=1);
namespace Sierra\Support;

use Sierra\Application;

abstract class ServiceProvider
{
    public function __construct(
        protected Application $app
    ) {}

    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
