<?php

declare(strict_types=1);

namespace AlizHarb\Themer\Tests\Unit;

use AlizHarb\Themer\Plugins\ModulesPlugin;
use AlizHarb\Themer\ThemeManager;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;

it('scans modules for themes', function () {
    if (! class_exists('AlizHarb\Modular\ModuleRegistry')) {
        $this->markTestSkipped('ModuleRegistry class not found.');
    }

    $plugin = new ModulesPlugin();
    expect($plugin->getId())->toBe('modules');

    $fixturePath = __DIR__.'/../fixtures/modules';
    $modulePath = $fixturePath.'/TestModule';
    $themePath = $modulePath.'/resources/theme';

    if (! File::exists($themePath)) {
        File::makeDirectory($themePath, 0755, true);
    }

    File::put($themePath.'/theme.json', (string) json_encode([
        'name' => 'module-theme',
        'asset_path' => 'modules/testmodule',
    ]));

    Config::set('modular.paths.modules', $fixturePath);

    // Setup simple activator stub
    $activator = new class() implements \AlizHarb\Modular\Contracts\Activator
    {
        public function enable(string $module): void {}

        public function disable(string $module): void {}

        public function hasStatus(string $module, bool $status): bool
        {
            return true;
        }

        public function setActive(string $module, bool $active): void {}

        public function setStatus(string $module, bool $status): void {}

        public function isEnabled(string $module): bool
        {
            return true;
        }

        public function delete(string $module): void {}

        public function reset(): void {}
    };

    app()->instance('TestActivator', $activator);
    Config::set('modular.activator', 'test');
    Config::set('modular.activators.test.class', 'TestActivator');
    Config::set('modular.cache.path', __DIR__.'/../fixtures/modules_cache.php'); // Prevent writing to real cache

    // Use real ModuleRegistry
    $registry = new \AlizHarb\Modular\ModuleRegistry();
    app()->instance('AlizHarb\Modular\ModuleRegistry', $registry);

    /** @var ThemeManager $manager */
    $manager = app(ThemeManager::class);

    $plugin->boot(app(), $manager, collect());

    expect($manager->all()->has('module-theme'))->toBeTrue()
        ->and($manager->all()->get('module-theme')->path)->toBe($modulePath.'/resources/theme');
});

afterAll(function () {
    $path = __DIR__.'/../fixtures';
    if (File::exists($path)) {
        File::deleteDirectory($path);
    }
});
