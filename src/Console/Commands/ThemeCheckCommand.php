<?php

declare(strict_types=1);

namespace AlizHarb\Themer\Console\Commands;

use AlizHarb\Themer\Theme;
use AlizHarb\Themer\ThemeManager;
use Illuminate\Console\Command;

class ThemeCheckCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'theme:check';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check theme hierarchy for integrity and circular dependencies';

    /**
     * Execute the console command.
     */
    public function handle(ThemeManager $manager): int
    {
        $themes = $manager->all();
        $status = self::SUCCESS;

        $this->components->info('Checking '.$themes->count().' themes for hierarchy integrity...');

        foreach ($themes as $theme) {
            // 1. Check Parent Existence
            if ($theme->parent && !$themes->has($theme->parent)) {
                $this->components->error("Theme [{$theme->name}] requires missing parent theme [{$theme->parent}]");
                $status = self::FAILURE;
            }

            // 2. Check for Circular Dependencies
            $path = [$theme->name];
            if ($this->hasCircularDependency($theme, $themes, $path)) {
                $this->components->error('Circular dependency detected: '.implode(' -> ', $path));
                $status = self::FAILURE;
            }

            // 3. Check for Required Modules (laravel-modular)
            $requires = $theme->config['requires'] ?? [];
            if (!empty($requires) && is_array($requires)) {
                foreach ($requires as $moduleName) {
                    if (!app()->bound('modular')) {
                        $this->components->warn("Theme [{$theme->name}] requires module [{$moduleName}] but laravel-modular is not installed.");

                        continue;
                    }

                    /** @var \AlizHarb\Modular\ModuleRegistry $registry */
                    $registry = app('modular');
                    $modules = $registry->getModules();

                    /** @var array{name: string, path: string, namespace: string, enabled?: bool}|null $module */
                    $module = collect($modules)->firstWhere('name', $moduleName);

                    if (!$module) {
                        $this->components->error("Theme [{$theme->name}] requires missing module [{$moduleName}]");
                        $status = self::FAILURE;
                    } elseif (!($module['enabled'] ?? true)) {
                        $this->components->error("Theme [{$theme->name}] requires module [{$moduleName}] but it is disabled.");
                        $status = self::FAILURE;
                    }
                }
            }
        }

        if ($status === self::SUCCESS) {
            $this->components->info('All themes passed hierarchy checks.');
        }

        return $status;
    }

    /**
     * Recursively check for circular dependencies.
     *
     * @param  \Illuminate\Support\Collection<string, Theme>  $themes
     * @param  array<int, string>  &$path
     */
    protected function hasCircularDependency(Theme $theme, \Illuminate\Support\Collection $themes, array &$path): bool
    {
        if (!$theme->parent) {
            return false;
        }

        if (in_array($theme->parent, $path)) {
            $path[] = $theme->parent;

            return true;
        }

        $parent = $themes->get($theme->parent);
        if ($parent instanceof Theme) {
            $path[] = $parent->name;

            return $this->hasCircularDependency($parent, $themes, $path);
        }

        return false;
    }
}
