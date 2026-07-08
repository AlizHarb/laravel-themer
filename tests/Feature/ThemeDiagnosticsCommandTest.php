<?php

declare(strict_types=1);

namespace AlizHarb\Themer\Tests\Feature;

use AlizHarb\Themer\ThemeManager;

it('outputs doctor diagnostics as json', function () {
    createThemeFixture('brand');

    app(ThemeManager::class)->scan(base_path('themes'));

    $this->artisan('theme:doctor --json')
        ->assertSuccessful()
        ->expectsOutputToContain('"status"');
});

it('shows theme status', function () {
    createThemeFixture('brand');

    app(ThemeManager::class)->scan(base_path('themes'));

    $this->artisan('theme:status')
        ->assertSuccessful()
        ->expectsOutputToContain('Laravel Themer Status');
});

it('debugs a theme as json', function () {
    createThemeFixture('brand', [
        'tokens' => ['color.primary' => '#111111'],
        'provides' => ['marketing'],
    ]);

    app(ThemeManager::class)->scan(base_path('themes'));

    $this->artisan('theme:debug brand --json')
        ->assertSuccessful()
        ->expectsOutputToContain('"provides"');
});

it('renders theme graph and why output', function () {
    createThemeFixture('base');
    createThemeFixture('child', ['parent' => 'base']);

    app(ThemeManager::class)->scan(base_path('themes'));

    $this->artisan('theme:graph')
        ->assertSuccessful()
        ->expectsOutputToContain('child -> base');

    $this->artisan('theme:why base')
        ->assertSuccessful()
        ->expectsOutputToContain('child');
});
