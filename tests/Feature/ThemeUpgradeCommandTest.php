<?php

declare(strict_types=1);

namespace AlizHarb\Themer\Tests\Feature;

use Illuminate\Support\Facades\File;

it('can upgrade all themes', function () {
    $themesPath = base_path('themes');
    $theme1 = $themesPath.'/theme-one';
    $theme2 = $themesPath.'/theme-two';

    if (File::exists($theme1)) {
        File::deleteDirectory($theme1);
    }
    if (File::exists($theme2)) {
        File::deleteDirectory($theme2);
    }

    File::makeDirectory($theme1, 0755, true);
    File::makeDirectory($theme2, 0755, true);
    File::put($theme1.'/theme.json', json_encode(['name' => 'Theme One', 'slug' => 'theme-one']));
    File::put($theme2.'/theme.json', json_encode(['name' => 'Theme Two', 'slug' => 'theme-two']));

    app('themer')->scan($themesPath);

    $this->artisan('theme:upgrade', ['--no-install' => true])
        ->assertExitCode(0)
        ->expectsOutputToContain('Upgrade completed!');

    expect(File::exists($theme1.'/package.json'))->toBeTrue()
        ->and(File::exists($theme1.'/vite.config.js'))->toBeTrue()
        ->and(File::exists($theme2.'/package.json'))->toBeTrue()
        ->and(File::exists($theme2.'/vite.config.js'))->toBeTrue();

    File::deleteDirectory($theme1);
    File::deleteDirectory($theme2);
});

it('can upgrade a specific theme', function () {
    $themesPath = base_path('themes');
    $theme1 = $themesPath.'/theme-one';
    $theme2 = $themesPath.'/theme-two';

    if (File::exists($theme1)) {
        File::deleteDirectory($theme1);
    }
    if (File::exists($theme2)) {
        File::deleteDirectory($theme2);
    }

    File::makeDirectory($theme1, 0755, true);
    File::makeDirectory($theme2, 0755, true);
    File::put($theme1.'/theme.json', json_encode(['name' => 'Theme One', 'slug' => 'theme-one']));
    File::put($theme2.'/theme.json', json_encode(['name' => 'Theme Two', 'slug' => 'theme-two']));

    app('themer')->scan($themesPath);

    $this->artisan('theme:upgrade', ['--theme' => 'theme-one', '--no-install' => true])
        ->assertExitCode(0)
        ->expectsOutputToContain('Upgrade completed!');

    expect(File::exists($theme1.'/package.json'))->toBeTrue()
        ->and(File::exists($theme1.'/vite.config.js'))->toBeTrue()
        ->and(File::exists($theme2.'/package.json'))->toBeFalse();

    File::deleteDirectory($theme1);
    File::deleteDirectory($theme2);
});

it('fails if specific theme does not exist', function () {
    $themesPath = base_path('themes');
    if (! File::exists($themesPath)) {
        File::makeDirectory($themesPath, 0755, true);
    }
    app('themer')->scan($themesPath);

    $this->artisan('theme:upgrade', ['--theme' => 'non-existent', '--no-install' => true])
        ->assertExitCode(1);
});

it('calls themer:install by default during upgrade', function () {
    $themesPath = base_path('themes');
    $theme1 = $themesPath.'/theme-one';
    if (File::exists($theme1)) {
        File::deleteDirectory($theme1);
    }
    File::makeDirectory($theme1, 0755, true);
    File::put($theme1.'/theme.json', json_encode(['name' => 'Theme One', 'slug' => 'theme-one']));

    app('themer')->scan($themesPath);

    $this->artisan('theme:upgrade', ['--theme' => 'theme-one'])
        ->expectsConfirmation('Would you like to show some love by starring the repo on GitHub? ⭐', 'no')
        ->assertExitCode(0);

    File::deleteDirectory($theme1);
});
