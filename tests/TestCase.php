<?php

namespace AlizHarb\Themer\Tests;

use AlizHarb\Themer\ThemeManager;
use AlizHarb\Themer\ThemeServiceProvider;
use Illuminate\Support\Facades\Facade;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        if ($this->app->bound('themer')) {
            app('themer')->reset();
        }

        Facade::setFacadeApplication($this->app);
    }

    protected function getPackageProviders($app): array
    {
        return [
            \Livewire\LivewireServiceProvider::class,
            ThemeServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app): void
    {
        // Bind themer in case the provider hasn't booted yet in some test contexts
        if (! $app->bound('themer')) {
            $app->singleton(ThemeManager::class, function ($app) {
                return new ThemeManager();
            });
            $app->alias(ThemeManager::class, 'themer');
        }
    }

    protected function tearDown(): void
    {
        $themesPath = base_path('themes');
        if (is_dir($themesPath)) {
            \Illuminate\Support\Facades\File::deleteDirectory($themesPath);
        }

        if (file_exists(base_path('.env'))) {
            @unlink(base_path('.env'));
        }

        parent::tearDown();
    }
}
