<?php

declare(strict_types=1);

namespace AlizHarb\Themer\Tests\Feature;

use AlizHarb\Themer\Theme;
use AlizHarb\Themer\ThemeManager;

it('supports multi-level inheritance for views', function () {
    $manager = app(ThemeManager::class);

    $gp = new Theme('grandparent', 'grandparent', '/grandparent/path', hasViews: true);
    $p = new Theme('parent', 'parent', '/parent/path', parent: 'grandparent', hasViews: true);
    $c = new Theme('child', 'child', '/child/path', parent: 'parent', hasViews: true);

    $manager->register($gp);
    $manager->register($p);
    $manager->register($c);

    $manager->set('child');

    $paths = $manager->getThemeViewPaths();

    expect($paths)->toHaveCount(3)
        ->and($paths[0])->toContain('child')
        ->and($paths[1])->toContain('parent')
        ->and($paths[2])->toContain('grandparent');
});

it('supports multi-level inheritance for livewire', function () {
    // This is more complex as it involves Livewire::addNamespace
    // We can verify that our internal parents logic works.
    $manager = app(ThemeManager::class);

    $gp = new Theme('gp', 'gp', '/path/gp', hasLivewire: true);
    $p = new Theme('p', 'p', '/path/p', parent: 'gp', hasLivewire: true);
    $c = new Theme('c', 'c', '/path/c', parent: 'p', hasLivewire: true);

    $manager->register($gp);
    $manager->register($p);
    $manager->register($c);

    // Using reflection to check if parents are correctly identified
    $ref = new \ReflectionClass($manager);
    $method = $ref->getMethod('getThemeParents');
    $method->setAccessible(true);

    $parents = $method->invoke($manager, $c);

    expect($parents)->toHaveCount(2)
        ->and($parents[0]->name)->toBe('p')
        ->and($parents[1]->name)->toBe('gp');
});
