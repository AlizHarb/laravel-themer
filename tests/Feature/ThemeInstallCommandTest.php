<?php

use Illuminate\Support\Facades\File;

beforeEach(function () {
    //
});

afterEach(function () {
    File::delete(base_path('vite.config.js'));
    File::delete(base_path('vite.themer.js'));
    File::deleteDirectory(base_path('themes'));
});

it('can install themer and configure vite automatically', function () {
    $viteConfig = base_path('vite.config.js');
    $viteLoader = base_path('vite.themer.js');

    File::ensureDirectoryExists(dirname($viteConfig));

    File::put($viteConfig, "import { defineConfig } from 'vite';\nimport laravel from 'laravel-vite-plugin';\n\nexport default defineConfig({\n    plugins: [\n        laravel({\n            input: ['resources/css/app.css', 'resources/js/app.js'],\n            refresh: true,\n        }),\n    ],\n});");

    $this->artisan('themer:install')
        ->expectsOutput('Publishing resources...')
        ->expectsConfirmation('Would you like to automatically configure vite.config.js?', 'yes')
        ->expectsConfirmation('Would you like to show some love by starring the repo on GitHub? ⭐', 'no')
        ->assertExitCode(0);

    expect(File::exists($viteLoader))->toBeTrue();
    expect(File::isDirectory(base_path('themes')))->toBeTrue();

    $config = File::get($viteConfig);
    expect($config)->toContain("import { themerLoader } from './vite.themer.js'");
    expect($config)->toContain('...themerLoader.inputs()');
});
