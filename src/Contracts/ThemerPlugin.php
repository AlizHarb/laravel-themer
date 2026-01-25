<?php

declare(strict_types=1);

namespace AlizHarb\Themer\Contracts;

use AlizHarb\Themer\ThemeManager;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Collection;

/**
 * Interface for Laravel Themer plugins.
 */
interface ThemerPlugin
{
    /**
     * Get the unique identifier for the plugin.
     */
    public function getId(): string;

    /**
     * Register the plugin services.
     */
    public function register(Application $app, ThemeManager $manager, Collection $themes): void;

    /**
     * Bootstrap the plugin services.
     */
    public function boot(Application $app, ThemeManager $manager, Collection $themes): void;
}
