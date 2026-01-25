<?php

declare(strict_types=1);

namespace AlizHarb\Themer\Tests\Unit;

use AlizHarb\Themer\Theme;
use AlizHarb\Themer\ThemeManager;
use AlizHarb\Themer\Themer;
use Illuminate\Support\Facades\File;

it('resolves standard view if no theme or override', function () {
    expect(Themer::resolve('welcome'))->toBe('welcome');
});

it('resolves theme view if exists', function () {
    /** @var ThemeManager $manager */
    $manager = app(ThemeManager::class);

    if (!File::exists(__DIR__.'/../fixtures/theme/resources/views')) {
        File::makeDirectory(__DIR__.'/../fixtures/theme/resources/views', 0755, true);
        File::put(__DIR__.'/../fixtures/theme/resources/views/test.blade.php', 'Theme view content');
    }

    $themePath = realpath(__DIR__.'/../fixtures/theme');
    $theme = new Theme('fixture', $themePath);
    $manager->register($theme);
    $manager->set('fixture');

    // Verify file exists
    $viewFile = $themePath.'/resources/views/test.blade.php';
    if (!file_exists($viewFile)) {
        @mkdir(dirname($viewFile), 0755, true);
        file_put_contents($viewFile, 'Theme view content');
    }

    // Register namespace manually for testing isolation
    view()->addNamespace('theme', $themePath.'/resources/views');

    expect(view()->exists('theme::test'))->toBeTrue('View theme::test should exist');
    expect(Themer::resolve('test'))->toBe('theme::test');
});

it('falls back to default if theme view missing', function () {
    /** @var ThemeManager $manager */
    $manager = app(ThemeManager::class);
    $theme = new Theme('fixture', __DIR__.'/../fixtures/theme');
    $manager->register($theme);
    $manager->set('fixture');

    expect(Themer::resolve('missing_in_theme'))->toBe('missing_in_theme');
});

afterAll(function () {
    $path = __DIR__.'/../fixtures';
    if (File::exists($path)) {
        File::deleteDirectory($path);
    }
});
