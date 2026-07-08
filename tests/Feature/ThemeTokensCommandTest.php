<?php

declare(strict_types=1);

namespace AlizHarb\Themer\Tests\Feature;

use AlizHarb\Themer\ThemeManager;

it('exposes theme tokens through command and helpers', function () {
    createThemeFixture('brand', [
        'tokens' => [
            'color.primary' => '#2563eb',
            'radius.card' => '1rem',
        ],
    ]);

    $manager = app(ThemeManager::class);
    $manager->scan(base_path('themes'));
    $manager->set('brand');

    expect(theme_token('color.primary'))->toBe('#2563eb')
        ->and(theme_tokens())->toHaveKey('radius.card');

    $this->artisan('theme:tokens brand --css')
        ->assertSuccessful()
        ->expectsOutputToContain('--theme-color-primary: #2563eb;');
});
