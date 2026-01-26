<?php

declare(strict_types=1);

namespace AlizHarb\Themer\Tests\Unit;

use AlizHarb\Themer\Theme;
use AlizHarb\Themer\ThemeManager;
use AlizHarb\Themer\Traits\HasThemeLayout;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\View;
use Livewire\Component;

it('resolves layout using themer', function () {
    /** @var ThemeManager $manager */
    $manager = app('themer');

    $component = new class () extends Component {
        use HasThemeLayout;

        public string $layout = 'layouts.app';
    };

    // Default behavior without theme
    expect($component->layout())->toBe('layouts.app');

    // With active theme
    $tempPath = __DIR__.'/../fixtures/views';
    if (!File::exists($tempPath.'/layouts')) {
        File::makeDirectory($tempPath.'/layouts', 0755, true);
        File::put($tempPath.'/layouts/app.blade.php', 'Theme layout content');
    }

    // Register the namespace manually for the test
    View::addNamespace('theme', $tempPath);

    $theme = new Theme('dark', 'dark', '/path/to/dark');
    $manager->register($theme);
    $manager->set('dark');

    // Themer::resolve('layouts.app') should find 'theme::layouts.app'
    // because we added the namespace 'theme' to $tempPath which has layouts/app.blade.php
    expect($component->layout())->toBe('theme::layouts.app');
});

afterAll(function () {
    $path = __DIR__.'/../fixtures';
    if (File::exists($path)) {
        File::deleteDirectory($path);
    }
});
