<?php

declare(strict_types=1);

namespace AlizHarb\Themer\Tests\Feature;

use Illuminate\Support\Facades\File;

it('can generate a new theme structure', function () {
    $themeName = 'Golden Theme';
    $slug = 'golden-theme';
    $themesPath = base_path('themes');
    $fullPath = $themesPath.DIRECTORY_SEPARATOR.$slug;

    if (File::exists($fullPath)) {
        File::deleteDirectory($fullPath);
    }

    $this->artisan("theme:make \"{$themeName}\"")
        ->assertExitCode(0)
        ->expectsOutputToContain("Theme [{$themeName}] created successfully");

    expect(File::isDirectory($fullPath))->toBeTrue()
        ->and(File::exists($fullPath.'/theme.json'))->toBeTrue()
        ->and(File::isDirectory($fullPath.'/app/Livewire'))->toBeTrue()
        ->and(File::isDirectory($fullPath.'/resources/views/livewire'))->toBeTrue()
        ->and(File::exists($fullPath.'/resources/assets/css/app.css'))->toBeTrue();

    $config = json_decode(File::get($fullPath.'/theme.json'), true);
    expect($config['name'])->toBe($themeName)
        ->and($config['asset_path'])->toBe('themes/'.$slug);

    File::deleteDirectory($fullPath);
});

it('fails if theme already exists', function () {
    $slug = 'existing-theme';
    $fullPath = base_path('themes').DIRECTORY_SEPARATOR.$slug;
    File::makeDirectory($fullPath, 0755, true);

    $this->artisan('theme:make "Existing Theme"')
        ->assertExitCode(1)
        ->expectsOutputToContain("Theme [{$slug}] already exists");

    File::deleteDirectory($fullPath);
});
