<?php

declare(strict_types=1);

namespace AlizHarb\Themer\Tests\Feature;

use Illuminate\Support\Facades\File;

it('successfully runs the linting process on a specific theme', function () {
    $themePath = base_path('themes/lint-tester');

    if (! File::exists($themePath)) {
        File::makeDirectory($themePath, 0755, true);
    }

    File::put($themePath.'/theme.json', json_encode([
        'name' => 'Lint Tester',
        'slug' => 'lint-tester',
    ]));

    File::put($themePath.'/package.json', json_encode([
        'scripts' => [
            'format' => 'echo formatted',
        ],
    ]));

    app('themer')->scan(base_path('themes'));

    $this->artisan('theme:lint lint-tester')
        ->assertExitCode(0)
        ->expectsOutputToContain('Linting completed successfully');

    File::deleteDirectory($themePath);
});

it('gracefully skips asset linting if no format script is defined', function () {
    $themePath = base_path('themes/lint-missing');

    if (! File::exists($themePath)) {
        File::makeDirectory($themePath, 0755, true);
    }

    File::put($themePath.'/theme.json', json_encode([
        'name' => 'Lint Missing',
        'slug' => 'lint-missing',
    ]));

    // Explicitly target assets mode which will fail gracefully
    app('themer')->scan(base_path('themes'));

    $this->artisan('theme:lint lint-missing --assets')
        ->assertExitCode(0)
        ->expectsOutputToContain('Skipping');

    File::deleteDirectory($themePath);
});
