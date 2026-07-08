<?php

declare(strict_types=1);

namespace AlizHarb\Themer\Tests\Feature;

use AlizHarb\Themer\ThemeManager;

it('generates preview urls for inactive themes', function () {
    createThemeFixture('brand');

    app(ThemeManager::class)->scan(base_path('themes'));

    $this->artisan('theme:preview brand --path=/checkout')
        ->assertSuccessful()
        ->expectsOutputToContain('preview_theme=brand');
});
