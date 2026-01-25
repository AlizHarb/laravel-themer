<?php

declare(strict_types=1);

namespace AlizHarb\Themer\Tests\Feature;

use AlizHarb\Themer\Theme;
use AlizHarb\Themer\ThemeManager;
use Illuminate\Support\Facades\File;

it('can activate a theme', function () {
    /** @var ThemeManager $manager */
    $manager = app('themer');
    $theme = new Theme('blue', '/path/blue');
    $manager->register($theme);

    // Use a temp env file
    $tempDir = __DIR__.'/../temp';
    if (!is_dir($tempDir)) {
        mkdir($tempDir, 0777, true);
    }

    $envPath = $tempDir.'/.env.test';
    file_put_contents($envPath, "THEME=old\n");

    $this->app->useEnvironmentPath($tempDir);
    $this->app->loadEnvironmentFrom('.env.test');

    $this->artisan('theme:activate blue')
        ->assertExitCode(0);

    expect($manager->getActiveTheme() === null ? null : $manager->getActiveTheme()->name)->toBe('blue')
        ->and(file_get_contents($envPath))->toContain('THEME="blue"');

    unlink($envPath);
});

it('fails to activate non-existent theme', function () {
    $this->artisan('theme:activate ghost')
        ->assertExitCode(1)
        ->expectsOutputToContain('Theme [ghost] not found.');
});

it('prompts for theme if none provided', function () {
    /** @var ThemeManager $manager */
    $manager = app('themer');
    $manager->register(new Theme('t1', '/p1'));
    $manager->register(new Theme('t2', '/p2'));

    $this->artisan('theme:activate')
        ->expectsChoice('Which theme do you want to activate?', 't2', ['t1', 't2'])
        ->assertExitCode(0);

    expect($manager->getActiveTheme() === null ? null : $manager->getActiveTheme()->name)->toBe('t2');
});
