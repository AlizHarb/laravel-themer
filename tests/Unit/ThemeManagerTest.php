<?php

declare(strict_types=1);

namespace AlizHarb\Themer\Tests\Unit;

use AlizHarb\Themer\Exceptions\ThemeNotFoundException;
use AlizHarb\Themer\Theme;
use AlizHarb\Themer\ThemeManager;
use Illuminate\Support\Facades\View;

it('can register a theme', function () {
    $manager = new ThemeManager();
    $theme = new Theme('default', 'default', '/path/to/theme');

    $manager->register($theme);

    expect($manager->all()->has('default'))->toBeTrue()
        ->and($manager->all()->get('default'))->toBe($theme);
});

it('can set an active theme', function () {
    $manager = new ThemeManager();
    $theme = new Theme('dark', 'dark', '/path/to/dark');
    $manager->register($theme);

    $manager->set('dark');

    expect($manager->getActiveTheme())->toBe($theme);
});

it('throws exception when setting non-existent theme', function () {
    $manager = new ThemeManager();

    expect(fn () => $manager->set('ghost'))
        ->toThrow(ThemeNotFoundException::class, 'Theme [ghost] not found.');
});

it('registers theme views when set', function () {
    $manager = new ThemeManager();
    $themePath = __DIR__.'/../fixtures/theme';

    if (! file_exists($themePath)) {
        mkdir($themePath.'/resources/views', 0777, true);
    }

    $theme = new Theme('fixture', 'fixture', $themePath, hasViews: true);
    $manager->register($theme);

    $manager->set('fixture');

    $finder = View::getFinder();
    /** @var array<string, array<int, string>> $hints */
    $hints = $finder instanceof \Illuminate\View\FileViewFinder ? $finder->getHints() : [];

    expect($hints)->toHaveKey('theme')
        ->and($hints['theme'][0])->toContain($themePath.'/resources/views');
});
