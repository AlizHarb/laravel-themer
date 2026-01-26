<?php

declare(strict_types=1);

namespace AlizHarb\Themer\Tests\Feature;

use AlizHarb\Themer\Theme;
use AlizHarb\Themer\ThemeManager;

it('supports ephemeral theme switching via forTheme', function () {
    $manager = app(ThemeManager::class);
    $manager->register(new Theme('default', 'default', '/path/default'));
    $manager->register(new Theme('branded', 'branded', '/path/branded'));

    $manager->set('default');
    expect($manager->getActiveTheme()->name)->toBe('default');

    $result = $manager->forTheme('branded', function ($manager) {
        expect($manager->getActiveTheme()->name)->toBe('branded');

        return 'success';
    });

    expect($result)->toBe('success')
        ->and($manager->getActiveTheme()->name)->toBe('default');
});
