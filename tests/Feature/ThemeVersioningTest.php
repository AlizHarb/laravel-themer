<?php

declare(strict_types=1);

namespace AlizHarb\Themer\Tests\Feature;

use AlizHarb\Themer\ThemeManager;
use Illuminate\Support\Facades\File;

it('extracts version from theme.json', function () {
    $tempDir = __DIR__.'/../temp/version-test';
    if (! is_dir($tempDir)) {
        mkdir($tempDir, 0777, true);
    }

    $config = [
        'name' => 'Versioned Theme',
        'version' => '1.2.3',
    ];

    File::put($tempDir.'/theme.json', json_encode($config));

    $manager = new ThemeManager();
    $manager->scan(__DIR__.'/../temp');

    $theme = $manager->all()->get('Versioned Theme');

    expect($theme)->not->toBeNull()
        ->and($theme->version)->toBe('1.2.3');

    File::deleteDirectory($tempDir);
});

it('defaults to 1.0.0 if version is missing', function () {
    $tempDir = __DIR__.'/../temp/default-version-test';
    if (! is_dir($tempDir)) {
        mkdir($tempDir, 0777, true);
    }

    $config = [
        'name' => 'Legacy Theme',
    ];

    File::put($tempDir.'/theme.json', json_encode($config));

    $manager = new ThemeManager();
    $manager->scan(__DIR__.'/../temp');

    $theme = $manager->all()->get('Legacy Theme');

    expect($theme)->not->toBeNull()
        ->and($theme->version)->toBe('1.0.0');

    File::deleteDirectory($tempDir);
});
