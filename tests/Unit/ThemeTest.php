<?php

declare(strict_types=1);

namespace AlizHarb\Themer\Tests\Unit;

use AlizHarb\Themer\Theme;

it('can be instantiated with basic properties', function () {
    $theme = new Theme(
        name: 'test-theme',
        slug: 'test-theme',
        path: '/path/to/theme',
        assetPath: 'custom/assets',
        parent: 'parent-theme',
        config: ['version' => '1.0.0'],
        hasViews: true,
        hasTranslations: false,
        hasProvider: true,
        hasLivewire: true
    );

    expect($theme->name)->toBe('test-theme')
        ->and($theme->slug)->toBe('test-theme')
        ->and($theme->path)->toBe('/path/to/theme')
        ->and($theme->assetPath)->toBe('custom/assets')
        ->and($theme->parent)->toBe('parent-theme')
        ->and($theme->config)->toBe(['version' => '1.0.0'])
        ->and($theme->hasViews)->toBeTrue()
        ->and($theme->hasTranslations)->toBeFalse()
        ->and($theme->hasProvider)->toBeTrue()
        ->and($theme->hasLivewire)->toBeTrue();
});

it('works with minimal properties', function () {
    $theme = new Theme('minimal', 'minimal', '/min/path');

    expect($theme->name)->toBe('minimal')
        ->and($theme->path)->toBe('/min/path')
        ->and($theme->assetPath)->toBeEmpty()
        ->and($theme->parent)->toBeNull()
        ->and($theme->config)->toBe([]);
});
