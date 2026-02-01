<?php

declare(strict_types=1);

namespace AlizHarb\Themer\Tests\Feature;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

test('it can generate theme with enhanced metadata and screenshots', function () {
    $themeName = 'PremiumTheme';
    $slug = 'premiumtheme';
    $themesPath = config('themer.themes_path', base_path('themes'));
    $path = $themesPath.'/'.$slug;

    if (File::exists($path)) {
        File::deleteDirectory($path);
    }

    Artisan::call('theme:make', [
        'name' => $themeName,
        '--description' => 'A high-end luxury theme',
        '--author' => 'Ali Harb',
        '--tags' => 'luxury,premium,gold',
        '--provider' => true,
    ]);

    expect(File::isDirectory($path))->toBeTrue();
    expect(File::exists($path.'/theme.json'))->toBeTrue();
    expect(File::exists($path.'/ThemeServiceProvider.php'))->toBeTrue();
    expect(File::isDirectory($path.'/resources/assets/screenshots'))->toBeTrue();
    expect(File::exists($path.'/resources/assets/screenshots/screenshot-light.png'))->toBeTrue();
    expect(File::exists($path.'/resources/assets/screenshots/screenshot-dark.png'))->toBeTrue();

    $config = json_decode(File::get($path.'/theme.json'), true);
    expect($config['name'])->toBe($themeName);
    expect($config['description'])->toBe('A high-end luxury theme');
    expect($config['author'])->toBe('Ali Harb');
    expect($config['tags'])->toEqual(['luxury', 'premium', 'gold']);
    expect($config['screenshots'])->toContain('resources/assets/screenshots/screenshot-light.png');

    $providerContent = File::get($path.'/ThemeServiceProvider.php');
    expect($providerContent)->toContain('namespace Theme\PremiumTheme;');

    // Cleanup
    File::deleteDirectory($path);
});
