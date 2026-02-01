<?php

declare(strict_types=1);

namespace AlizHarb\Themer\Tests\Feature;

use AlizHarb\Themer\Theme;
use AlizHarb\Themer\ThemeManager;
use Illuminate\Support\Facades\File;

it('can publish theme assets', function () {
    /** @var ThemeManager $manager */
    $manager = app('themer');
    $themePath = base_path('themes/publishing-theme');
    File::ensureDirectoryExists($themePath.'/resources/assets');
    File::put($themePath.'/resources/assets/style.css', '/* test */');

    expect(File::exists($themePath.'/resources/assets/style.css'))->toBeTrue('Source file was not created');

    $theme = new Theme('publishing-theme', 'publishing-theme', $themePath);
    $manager->register($theme);

    \Illuminate\Support\Facades\Config::set('themer.assets.symlink', false);

    $destination = public_path('themes/publishing-theme');
    if (File::exists($destination)) {
        File::deleteDirectory($destination);
    }

    $this->artisan('theme:publish publishing-theme')
        ->assertExitCode(0);

    clearstatcache();
    $files = File::allFiles($themePath.'/resources/assets');
    $glob = glob($themePath.'/resources/assets/*');

    // Ensure config is set
    \Illuminate\Support\Facades\Config::set('themer.assets.path', 'themes');

    clearstatcache();
    $path = $themePath.'/resources/assets/style.css';
    if (! file_exists($path)) {
        @mkdir(dirname($path), 0755, true);
        file_put_contents($path, '/* test */');
    }

    if (! File::exists($destination.'/style.css')) {
        // Fallback check
    }

    expect(File::exists($destination.'/style.css'))->toBeTrue('Asset was not published to '.$destination);

    File::deleteDirectory($themePath);
    File::deleteDirectory($destination);
});

it('publishes all themes if none specified', function () {
    /** @var ThemeManager $manager */
    $manager = app('themer');

    $t1Path = base_path('themes/t1');
    $t2Path = base_path('themes/t2');
    File::makeDirectory($t1Path.'/resources/assets', 0755, true);
    File::makeDirectory($t2Path.'/resources/assets', 0755, true);
    File::put($t1Path.'/resources/assets/f1.txt', '1');
    File::put($t2Path.'/resources/assets/f2.txt', '2');

    $manager->register(new Theme('t1', 't1', $t1Path));
    $manager->register(new Theme('t2', 't2', $t2Path));

    $this->artisan('theme:publish')
        ->assertExitCode(0);

    expect(File::exists(public_path('themes/t1/f1.txt')))->toBeTrue()
        ->and(File::exists(public_path('themes/t2/f2.txt')))->toBeTrue();

    File::deleteDirectory($t1Path);
    File::deleteDirectory($t2Path);
    File::deleteDirectory(public_path('themes/t1'));
    File::deleteDirectory(public_path('themes/t2'));
});
