<?php

declare(strict_types=1);

namespace AlizHarb\Themer\Console\Commands;

use AlizHarb\Modular\ModuleRegistry;
use AlizHarb\Themer\Theme;
use AlizHarb\Themer\ThemeManager;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;

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
        /** @var Collection<string, Theme> $themes */
        $themes = $manager->all();
        $status = self::SUCCESS;

        $this->components->info('Checking '.$themes->count().' themes for hierarchy integrity...');

        foreach ($themes as $theme) {
            // 1. Check Parent Existence
            if ($theme->parent && ! $themes->has($theme->parent)) {
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
            if (! empty($requires) && is_array($requires)) {
                foreach ($requires as $moduleName) {
                    if (! app()->bound('modular')) {
                        $this->components->warn("Theme [{$theme->name}] requires module [{$moduleName}] but laravel-modular is not installed.");

                        continue;
                    }

                    /** @var ModuleRegistry $registry */
                    $registry = app('modular');
                    /** @var array<int, array{name: string, path: string, namespace: string, enabled?: bool}> $modules */
                    $modules = $registry->getModules();

                    /** @var array{name: string, path: string, namespace: string, enabled?: bool}|null $module */
                    $module = collect($modules)->firstWhere('name', $moduleName);

                    if (! $module) {
                        $this->components->error("Theme [{$theme->name}] requires missing module [{$moduleName}]");
                        $status = self::FAILURE;
                    } elseif (! ($module['enabled'] ?? true)) {
                        $this->components->error("Theme [{$theme->name}] requires module [{$moduleName}] but it is disabled.");
                        $status = self::FAILURE;
                    }
                }
            }

            // 4. Asset Health Checks
            if (! $theme->parent) {
                if (! File::exists($theme->path.'/package.json')) {
                    $this->components->warn("Theme [{$theme->name}] is missing [package.json]. Run theme:upgrade to fix.");
                }
                if (! File::exists($theme->path.'/vite.config.js')) {
                    $this->components->warn("Theme [{$theme->name}] is missing [vite.config.js]. Run theme:upgrade to fix.");
                }
            }

            // 5. Screenshot validation
            foreach ($theme->screenshots as $screenshot) {
                if (! File::exists($theme->path.'/'.$screenshot)) {
                    $this->components->error("Theme [{$theme->name}] references missing screenshot [{$screenshot}]");
                    $status = self::FAILURE;
                }
            }

            // 6. Config Validation
            if (empty($theme->author)) {
                $this->components->warn("Theme [{$theme->name}] has no author defined in [theme.json].");
            }

            // 7. Optimization Tips (NPM Workspaces)
            if (File::isDirectory($theme->path.'/node_modules')) {
                $this->components->warn("Theme [{$theme->name}] contains its own [node_modules]. Recommendation: Remove it and use NPM Workspaces for faster builds and zero storage overhead.");
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
     * @param Collection<string, Theme> $themes
     * @param array<int, string> &$path
     */
    protected function hasCircularDependency(Theme $theme, Collection $themes, array &$path): bool
    {
        if (! $theme->parent) {
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
