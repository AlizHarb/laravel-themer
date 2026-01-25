<?php

declare(strict_types=1);

namespace AlizHarb\Themer\Tests\Feature;

use AlizHarb\Themer\Theme;
use AlizHarb\Themer\ThemeManager;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\View;

it('supports ephemeral theme switching via forTheme', function () {
    $manager = app(ThemeManager::class);
    $manager->register(new Theme('default', '/path/default'));
    $manager->register(new Theme('branded', '/path/branded'));

    $manager->set('default');
    expect($manager->getActiveTheme()->name)->toBe('default');

    $result = $manager->forTheme('branded', function ($manager) {
        expect($manager->getActiveTheme()->name)->toBe('branded');

        return 'success';
    });

    expect($result)->toBe('success')
        ->and($manager->getActiveTheme()->name)->toBe('default');
});

it('discovers blade components directory', function () {
    $tempDir = __DIR__.'/../temp/blade-test';
    $compDir = $tempDir.'/resources/views/components';

    if (!is_dir($compDir)) {
        mkdir($compDir, 0777, true);
    }

    File::put($compDir.'/button.blade.php', '<button>{{ $slot }}</button>');

    $manager = app(ThemeManager::class);
    $theme = new Theme('blade-theme', $tempDir, hasViews: true);
    $manager->register($theme);

    $manager->set('blade-theme');

    $finder = View::getFinder();
    /** @var array<string, array<int, string>> $hints */
    $hints = $finder instanceof \Illuminate\View\FileViewFinder ? $finder->getHints() : [];

    expect($hints)->toHaveKey('theme-components')
        ->and($hints['theme-components'][0])->toContain('components');

    File::deleteDirectory($tempDir);
});
