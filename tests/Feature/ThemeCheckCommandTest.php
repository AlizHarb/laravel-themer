<?php

declare(strict_types=1);

namespace AlizHarb\Themer\Tests\Feature;

use AlizHarb\Themer\Theme;
use AlizHarb\Themer\ThemeManager;

it('passes when hierarchy is valid', function () {
    $manager = app(ThemeManager::class);
    $manager->register(new Theme('parent', '/path/1'));
    $manager->register(new Theme('child', '/path/2', parent: 'parent'));

    $this->artisan('theme:check')
        ->assertSuccessful()
        ->expectsOutputToContain('All themes passed hierarchy checks.');
});

it('fails when parent is missing', function () {
    $manager = app(ThemeManager::class);
    $manager->register(new Theme('child', '/path/2', parent: 'missing'));

    $this->artisan('theme:check')
        ->assertFailed()
        ->expectsOutputToContain('Theme [child] requires missing parent theme [missing]');
});

it('detects circular dependencies', function () {
    $manager = app(ThemeManager::class);
    $manager->register(new Theme('a', '/path/a', parent: 'b'));
    $manager->register(new Theme('b', '/path/b', parent: 'a'));

    $this->artisan('theme:check')
        ->assertFailed()
        ->expectsOutputToContain('Circular dependency detected: a -> b -> a');
});

it('detects deep circular dependencies', function () {
    $manager = app(ThemeManager::class);
    $manager->register(new Theme('x', '/path/x', parent: 'y'));
    $manager->register(new Theme('y', '/path/y', parent: 'z'));
    $manager->register(new Theme('z', '/path/z', parent: 'x'));

    $this->artisan('theme:check')
        ->assertFailed()
        ->expectsOutputToContain('Circular dependency detected: x -> y -> z -> x');
});
