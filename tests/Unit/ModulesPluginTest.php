<?php

declare(strict_types=1);

namespace AlizHarb\Themer\Tests\Unit;

use AlizHarb\Themer\Plugins\ModulesPlugin;
use AlizHarb\Themer\ThemeManager;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;

it('scans modules for themes', function () {
    if (!class_exists('AlizHarb\Modular\ModuleRegistry')) {
        $this->markTestSkipped('ModuleRegistry class not found.');
    }

    $plugin = new ModulesPlugin();
    expect($plugin->getId())->toBe('modules');

    $fixturePath = __DIR__.'/../fixtures/modules';
    if (!File::exists($fixturePath)) {
        File::makeDirectory($fixturePath, 0755, true);
    }

    $modulePath = $fixturePath.'/TestModule';
    if (!File::exists($modulePath.'/resources/theme')) {
        File::makeDirectory($modulePath.'/resources/theme', 0755, true);
    }

    File::put($modulePath.'/resources/theme/theme.json', (string) json_encode([
        'name' => 'module-theme',
        'asset_path' => 'modules/testmodule',
    ]));

    Config::set('modular.paths.modules', $fixturePath);

    /** @var mixed $registry */
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
