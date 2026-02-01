<?php

declare(strict_types=1);

namespace AlizHarb\Themer\Tests\Feature;

use AlizHarb\Themer\ThemeManager;
use Illuminate\Support\Facades\File;

it('can clone a theme', function () {
    $themesPath = base_path('themes');
    $sourcePath = $themesPath.'/source-theme';
    $targetPath = $themesPath.'/cloned-theme';

    if (File::exists($sourcePath)) {
        File::deleteDirectory($sourcePath);
    }
    if (File::exists($targetPath)) {
        File::deleteDirectory($targetPath);
    }

    File::makeDirectory($sourcePath, 0755, true);
    File::put($sourcePath.'/theme.json', json_encode([
        'name' => 'Source Theme',
        'slug' => 'source-theme',
        'asset_path' => 'themes/source-theme',
    ]));

    app(ThemeManager::class)->reset();
    app(ThemeManager::class)->scan($themesPath);

    $this->artisan('theme:clone cloned-theme --theme=source-theme')
        ->assertExitCode(0)
        ->expectsOutputToContain('Theme [cloned-theme] created successfully');

    expect(File::exists($targetPath))->toBeTrue()
        ->and(File::exists($targetPath.'/theme.json'))->toBeTrue();

    $config = json_decode((string) File::get($targetPath.'/theme.json'), true);
    expect($config['name'])->toBe('cloned-theme')
        ->and($config['slug'])->toBe('cloned-theme');

    File::deleteDirectory($sourcePath);
    File::deleteDirectory($targetPath);
});

it('can delete a theme', function () {
    $themesPath = base_path('themes');
    $themePath = $themesPath.'/delete-me';

    if (File::exists($themePath)) {
        File::deleteDirectory($themePath);
    }

    File::makeDirectory($themePath, 0755, true);
    File::put($themePath.'/theme.json', json_encode([
        'name' => 'Delete Me',
        'slug' => 'delete-me',
        'asset_path' => 'themes/delete-me',
        'removable' => true,
    ]));

    app(ThemeManager::class)->reset();
    app(ThemeManager::class)->scan($themesPath);

    $this->artisan('theme:delete --theme=delete-me')
        ->expectsConfirmation('Are you sure you want to delete theme [Delete Me]? This action cannot be undone.', 'yes')
        ->assertExitCode(0)
        ->expectsOutputToContain('Theme [Delete Me] deleted successfully');

    expect(File::exists($themePath))->toBeFalse();
});

it('cannot delete non-removable theme without force', function () {
    $themesPath = base_path('themes');
    $themePath = $themesPath.'/stay-here';

    if (File::exists($themePath)) {
        File::deleteDirectory($themePath);
    }

    File::makeDirectory($themePath, 0755, true);
    File::put($themePath.'/theme.json', json_encode([
        'name' => 'Stay Here',
        'slug' => 'stay-here',
        'asset_path' => 'themes/stay-here',
        'removable' => false,
    ]));

    app(ThemeManager::class)->reset();
    app(ThemeManager::class)->scan($themesPath);

    $this->artisan('theme:delete --theme=stay-here')
        ->assertExitCode(1)
        ->expectsOutputToContain('Theme [Stay Here] is marked as non-removable.');

    expect(File::exists($themePath))->toBeTrue();

    File::deleteDirectory($themePath);
});
