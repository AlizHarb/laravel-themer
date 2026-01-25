<?php

declare(strict_types=1);

namespace AlizHarb\Themer\Tests\Feature;

use AlizHarb\Themer\Theme;
use AlizHarb\Themer\ThemeManager;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\View;

it('executes the full theme lifecycle flow', function () {
    /** @var ThemeManager $manager */
    $manager = app('themer');

    // 1. Create a theme using Artisan
    $this->artisan('theme:make "Flow Theme"')
        ->assertExitCode(0);

    $themePath = base_path('themes/flow-theme');
    expect(File::isDirectory($themePath))->toBeTrue();

    // 2. Scan and verify it's registered
    if (!File::exists(base_path('themes'))) {
        File::makeDirectory(base_path('themes'), 0755, true);
    }
    $manager->scan(base_path('themes'));

    // Debug info if fails
    if (!$manager->all()->has('flow-theme')) {
        // dump('Scan failed for flow-theme at '.base_path('themes'));
    }

    expect($manager->all()->has('Flow Theme'))->toBeTrue();

    // 3. Activate the theme
    // Set up temp env
    $tempDir = __DIR__ . '/../temp';
    if (!is_dir($tempDir)) {
        mkdir($tempDir, 0777, true);
    }

    $envPath = $tempDir . '/.env.flow';
    file_put_contents($envPath, "THEME=default\n");

    $this->app->useEnvironmentPath($tempDir);
    $this->app->loadEnvironmentFrom('.env.flow');

    $this->artisan('theme:activate "Flow Theme"')
        ->assertExitCode(0);

    // Manager doesn't update runtime config, but updates state
    expect($manager->getActiveTheme()->name)->toBe('Flow Theme');
    expect(file_get_contents($envPath))->toContain('THEME="Flow Theme"');

    unlink($envPath);

    // 4. Verify view resolution
    $manager->set('Flow Theme');

    // Create a view in the theme
    File::put($themePath.'/resources/views/overridden.blade.php', 'Overridden Content');

    // Manual registration of the 'theme' namespace for the test context
    View::addNamespace('theme', $themePath.'/resources/views');

    expect(\AlizHarb\Themer\Themer::resolve('overridden'))->toBe('theme::overridden');

    // 5. Publish assets
    $assetSource = $themePath.'/resources/assets';
    if (!file_exists($assetSource)) {
        mkdir($assetSource, 0755, true);
    }
    file_put_contents($assetSource.'/flow.js', 'console.log("flow");');

    Config::set('themer.assets.symlink', false);

    $this->artisan('theme:publish "Flow Theme"')
        ->assertExitCode(0);

    // Publisher uses theme name, not slug
    $publishedPath = public_path('themes/Flow Theme/flow.js');
    if (!File::exists($publishedPath) && File::exists(public_path('themes/flow-theme/flow.js'))) {
        $publishedPath = public_path('themes/flow-theme/flow.js');
    }

    expect(File::exists($publishedPath))->toBeTrue('Asset not published in flow at ' . $publishedPath);

    // Cleanup
    File::deleteDirectory($themePath);
    File::deleteDirectory(public_path('themes/flow-theme'));
    File::delete($envPath);
});
