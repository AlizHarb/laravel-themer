<?php

declare(strict_types=1);

namespace AlizHarb\Themer\Tests\Feature;

use AlizHarb\Themer\ThemeManager;
use Illuminate\Support\Facades\File;

it('can cache discovered themes', function () {
    /** @var ThemeManager $manager */
    $manager = app('themer');

    // Clear out any stale caches
    if (file_exists($manager->getCachePath())) {
        unlink($manager->getCachePath());
    }

    $themePath = base_path('themes/cache-test-theme');
    if (! File::exists($themePath)) {
        File::makeDirectory($themePath, 0755, true);
    }

    File::put($themePath.'/theme.json', json_encode([
        'name' => 'Cache Test Theme',
        'slug' => 'cache-test-theme',
    ]));

    config(['themer.themes_path' => base_path('themes')]);

    $cachePath = $manager->getCachePath();
    if (! is_dir(dirname($cachePath))) {
        mkdir(dirname($cachePath), 0755, true);
    }

    $this->artisan('theme:cache')
        ->assertExitCode(0);

    expect(file_exists($manager->getCachePath()))->toBeTrue();

    // Verify cache payload
    $cachedData = require $manager->getCachePath();
    expect($cachedData)->toBeArray()
        ->and($cachedData['themes']['cache-test-theme']['name'])->toBe('Cache Test Theme')
        ->and($cachedData['meta'])->toHaveKey('manifest_hashes');

    // Cleanup
    File::deleteDirectory($themePath);
    unlink($manager->getCachePath());
});

it('clears the cache successfully', function () {
    /** @var ThemeManager $manager */
    $manager = app('themer');

    $cachePath = $manager->getCachePath();
    file_put_contents($cachePath, '<?php return [];');
    expect(file_exists($cachePath))->toBeTrue();

    $this->artisan('theme:clear')
        ->assertExitCode(0);

    expect(file_exists($cachePath))->toBeFalse();
});
