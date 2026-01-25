<?php

declare(strict_types=1);

namespace AlizHarb\Themer\Tests\Feature;

use AlizHarb\Themer\Theme;
use AlizHarb\Themer\ThemeManager;

it('can list themes', function () {
    /** @var ThemeManager $manager */
    $manager = app('themer');
    $manager->register(new Theme('test-theme', '/path/test'));

    $this->artisan('theme:list')
        ->assertExitCode(0)
        ->expectsTable(['Name', 'Active', 'Path', 'Parent'], [
            ['test-theme', 'No', '/path/test', 'None'],
        ]);
});

it('shows warning when no themes found', function () {
    $this->artisan('theme:list')
        ->assertExitCode(0)
        ->expectsOutputToContain('No themes discovered.');
});

it('marks active theme in list', function () {
    /** @var ThemeManager $manager */
    $manager = app('themer');
    $theme = new Theme('active-theme', '/path/active');
    $manager->register($theme);
    $manager->set('active-theme');

    $this->artisan('theme:list')
        ->assertExitCode(0)
        ->expectsTable(['Name', 'Active', 'Path', 'Parent'], [
            ['active-theme', 'Yes', '/path/active', 'None'],
        ]);
});
