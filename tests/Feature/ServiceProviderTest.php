<?php

use Sierra\Application;
use Sierra\Support\ServiceProvider;

class TestProvider extends ServiceProvider
{
    public bool $registered = false;
    public bool $booted = false;

    public function register(): void
    {
        $this->registered = true;
    }

    public function boot(): void
    {
        $this->booted = true;
    }
}

test('service provider can be registered and booted', function () {
    $app = new Application(__DIR__);
    
    $provider = $app->registerProvider(TestProvider::class);
    
    expect($provider->registered)->toBeTrue();
    expect($provider->booted)->toBeFalse();
    
    $app->bootProviders();
    
    expect($provider->booted)->toBeTrue();
});
