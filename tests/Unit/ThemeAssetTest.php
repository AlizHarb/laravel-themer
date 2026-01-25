<?php

declare(strict_types=1);

namespace AlizHarb\Themer\Tests\Unit;

use AlizHarb\Themer\Theme;
use AlizHarb\Themer\ThemeAsset;
use AlizHarb\Themer\ThemeManager;

it('returns standard asset url if no theme active', function () {
    $url = ThemeAsset::url('css/app.css');

    expect($url)->toContain('css/app.css');
});

it('returns theme specific asset url when active', function () {
    /** @var ThemeManager $manager */
    $manager = app(ThemeManager::class);
    $theme = new Theme(name: 'dark', path: '/local/path', assetPath: 'themes/dark-mode');
    $manager->register($theme);
    $manager->set('dark');

    $url = ThemeAsset::url('js/main.js');

    expect($url)->toContain('themes/dark-mode/js/main.js');
});

it('defaults asset path to theme name if not provided', function () {
    /** @var ThemeManager $manager */
    $manager = app(ThemeManager::class);
    $theme = new Theme(name: 'retro', path: '/local/path');
    $manager->register($theme);
    $manager->set('retro');

    $url = ThemeAsset::url('img/logo.png');

    expect($url)->toContain('themes/retro/img/logo.png');
});
