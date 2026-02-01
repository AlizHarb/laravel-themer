<?php

declare(strict_types=1);

namespace AlizHarb\Themer\Tests\Feature;

use Illuminate\Support\Facades\File;

it('fails if theme does not exist for npm command', function () {
    app('themer')->scan(base_path('themes'));

    $this->artisan('theme:npm', ['args' => ['install'], '--theme' => 'non-existent'])
        ->assertExitCode(1)
        ->expectsOutputToContain('Theme [non-existent] not found!');
});

it('can run npm commands for a theme', function () {
    $themePath = base_path('themes/test-theme');

    if (File::exists($themePath)) {
        File::deleteDirectory($themePath);
    }

    File::makeDirectory($themePath, 0755, true);
    File::put($themePath.'/theme.json', json_encode(['name' => 'Test Theme', 'slug' => 'test-theme']));
    File::put($themePath.'/package.json', json_encode(['name' => 'test-theme', 'version' => '1.0.0']));

    app('themer')->scan(base_path('themes'));

    $manager = app('themer');
    expect($manager->find('test-theme'))->not->toBeNull();

    // Verify the command logic
    $this->artisan('theme:npm', ['args' => ['install', 'lodash'], '--theme' => 'test-theme'])
        ->expectsOutputToContain('Executing \'npm install lodash\' for theme: Test Theme')
        ->assertExitCode(0);

    File::deleteDirectory($themePath);
});
