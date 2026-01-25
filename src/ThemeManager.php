<?php

declare(strict_types=1);

namespace AlizHarb\Themer;

use AlizHarb\Themer\Exceptions\ThemeNotFoundException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Livewire\Livewire;

/**
 * The core Theme Manager class responsible for discovering,
 * activating, and managing theme-specific resources.
 */
class ThemeManager
{
    /**
     * The collection of discovered themes.
     *
     * @var Collection<string, Theme>
     */
    protected Collection $themes;

    /**
     * The currently active theme instance.
     */
    protected ?Theme $activeTheme = null;

    /**
     * Create a new ThemeManager instance.
     */
    public function __construct()
    {
        $this->themes = new Collection();
    }

    /**
     * Register a theme instance manually.
     */
    public function register(Theme $theme): void
    {
        $this->themes->put($theme->name, $theme);
    }

    /**
     * Scan the given directory for themes.
     */
    public function scan(string $path): void
    {
        $cachePath = $this->getCachePath();

        if (file_exists($cachePath)) {
            $themes = require $cachePath;
            foreach ($themes as $data) {
                $this->register(new Theme(
                    $data['name'],
                    $data['path'],
                    $data['assetPath'],
                    $data['parent'],
                    $data['config']
                ));
            }

            return;
        }

        if (!File::isDirectory($path)) {
            return;
        }

        $directories = File::directories($path);

        foreach ($directories as $directory) {
            $themeJsonPath = $directory.'/theme.json';

            if (File::exists($themeJsonPath)) {
                $json = File::get($themeJsonPath);
                $config = json_decode($json, true);

                if (!is_array($config)) {
                    continue;
                }

                $name = $config['name'] ?? basename((string) $directory);
                $assetPath = $config['asset_path'] ?? '';
                $parent = $config['parent'] ?? null;

                $theme = new Theme(
                    name: $name,
                    path: (string) $directory,
                    assetPath: $assetPath,
                    parent: $parent,
                    config: $config
                );

                $this->register($theme);
            }
        }
    }

    public function getCachePath(): string
    {
        return app()->bootstrapPath('cache/themes.php');
    }

    /**
     * Set the active theme.
     *
     *
     * @throws ThemeNotFoundException
     */
    public function set(string $themeName): void
    {
        $theme = $this->themes->get($themeName);

        if (!($theme instanceof Theme)) {
            throw ThemeNotFoundException::make($themeName);
        }

        \AlizHarb\Themer\Events\ThemeActivating::dispatch($themeName);

        $this->activeTheme = $theme;

        $this->registerResources($theme);

        \AlizHarb\Themer\Events\ThemeActivated::dispatch($theme);
    }

    /**
     * Register all theme resources (Views, Languages, Providers, Livewire, etc.)
     */
    protected function registerResources(Theme $theme): void
    {
        $this->registerThemeViews($theme);
        $this->registerThemeLanguages($theme);
        $this->registerThemeServiceProvider($theme);

        if ($theme->parent && $this->themes->has($theme->parent)) {
            $parent = $this->themes->get($theme->parent);
            if ($parent instanceof Theme) {
                $this->registerThemeLivewire($parent);
            }
        }

        $this->registerThemeLivewire($theme);
        $this->registerThemeVite($theme);

        if (config('themer.assets.publish_on_activate', true)) {
            $this->publishAssets($theme);
        }
    }

    /**
     * Register theme-specific Service Provider if it exists.
     */
    protected function registerThemeServiceProvider(Theme $theme): void
    {
        $providerPath = $theme->path.'/ThemeServiceProvider.php';

        if (File::exists($providerPath)) {
            require_once $providerPath;

            if (class_exists('ThemeServiceProvider')) {
                app()->register('ThemeServiceProvider');
            }
        }
    }

    /**
     * Register theme view namespaces and locations.
     */
    protected function registerThemeViews(Theme $theme): void
    {
        $paths = [$theme->path.'/resources/views'];

        if ($theme->parent && $this->themes->has($theme->parent)) {
            $parentTheme = $this->themes->get($theme->parent);
            if ($parentTheme instanceof Theme) {
                $paths[] = $parentTheme->path.'/resources/views';
            }
        }

        // 1. Register 'theme::' namespace
        app('view')->addNamespace('theme', $paths);

        // 2. Register 'layouts::' and 'pages::' namespaces if they exist
        $layoutPaths = collect($paths)
            ->map(fn (string $p): string => $p.'/layouts')
            ->filter(fn (string $p) => File::isDirectory($p))
            ->toArray();

        if (!empty($layoutPaths)) {
            app('view')->addNamespace('layouts', $layoutPaths);
        }

        $pagesPaths = collect($paths)
            ->map(fn (string $p): string => $p.'/livewire/pages')
            ->filter(fn (string $p) => File::isDirectory($p))
            ->toArray();

        if (!empty($pagesPaths)) {
            app('view')->addNamespace('pages', $pagesPaths);
        }

        // 3. Register for dynamic resolution (Active -> Parent -> App)
        /** @var \Illuminate\View\FileViewFinder $finder */
        $finder = app('view')->getFinder();

        foreach (array_reverse($paths) as $path) {
            if (File::exists($path)) {
                $finder->prependLocation($path);
            }
        }
    }

    /**
     * Register theme language namespaces.
     */
    protected function registerThemeLanguages(Theme $theme): void
    {
        $langPath = $theme->path.'/resources/lang';
        if (!File::exists($langPath)) {
            $langPath = $theme->path.'/lang';
        }

        if (File::exists($langPath)) {
            /** @var \Illuminate\Translation\Translator $translator */
            $translator = app()->make('translator');
            $lowerName = strtolower($theme->name);

            $translator->addNamespace($lowerName, $langPath);

            if ($theme === $this->activeTheme) {
                $translator->addNamespace('theme', $langPath);
            }

            $translator->addJsonPath($langPath);
        }

        if ($theme->parent && $this->themes->has($theme->parent)) {
            $parent = $this->themes->get($theme->parent);
            if ($parent instanceof Theme) {
                $this->registerThemeLanguages($parent);
            }
        }
    }

    /**
     * Publish or symlink theme assets to the public directory.
     */
    public function publishAssets(Theme $theme): void
    {
        $themeAssetsPath = $theme->path.'/resources/assets';
        if (!File::isDirectory($themeAssetsPath)) {
            $themeAssetsPath = $theme->path.'/assets';
        }

        if (!File::isDirectory($themeAssetsPath)) {
            return;
        }

        $publicPath = public_path(config('themer.assets.path', 'themes').'/'.$theme->name);

        if (!File::isDirectory(dirname($publicPath))) {
            File::makeDirectory(dirname($publicPath), 0755, true);
        }

        if (config('themer.assets.symlink', true) && function_exists('symlink')) {
            if (File::exists($publicPath)) {
                if (is_link($publicPath)) {
                    File::delete($publicPath);
                } else {
                    File::deleteDirectory($publicPath);
                }
            }

            @symlink($themeAssetsPath, $publicPath);
        } else {
            File::copyDirectory($themeAssetsPath, $publicPath);
        }
    }

    /**
     * Register theme Vite configuration.
     */
    protected function registerThemeVite(Theme $theme): void
    {
        // Placeholder for advanced Vite integration
    }

    /**
     * Get the active theme instance.
     */
    public function getActiveTheme(): ?Theme
    {
        return $this->activeTheme;
    }

    /**
     * Get the view paths for the active theme.
     *
     * @return string[]
     */
    public function getThemeViewPaths(): array
    {
        if (!$this->activeTheme instanceof \AlizHarb\Themer\Theme) {
            return [];
        }

        $paths = [$this->activeTheme->path.'/resources/views'];

        if ($this->activeTheme->parent && $this->themes->has($this->activeTheme->parent)) {
            $parentTheme = $this->themes->get($this->activeTheme->parent);
            if ($parentTheme instanceof Theme) {
                $paths[] = $parentTheme->path.'/resources/views';
            }
        }

        return $paths;
    }

    /**
     * Get all discovered themes.
     *
     * @return Collection<string, Theme>
     */
    public function all(): Collection
    {
        return $this->themes;
    }

    /**
     * Register theme-specific Livewire support.
     */
    protected function registerThemeLivewire(Theme $theme): void
    {
        if (!class_exists(Livewire::class)) {
            return;
        }

        $lowerName = strtolower($theme->name);
        $studlyName = Str::studly($theme->name);
        $livewirePath = $theme->path.'/app/Livewire';
        $livewireNamespace = sprintf('Theme\%s\Livewire', $studlyName);
        $livewireViewPath = $theme->path.'/resources/views/livewire';

        // 1. Register Theme-Specific Namespace
        Livewire::addNamespace(
            $lowerName,
            $livewireViewPath,
            $livewireNamespace,
            $livewirePath,
            $livewireViewPath
        );

        // 2. Register Global Alias Baseline
        if (File::isDirectory($theme->path.'/resources/views/livewire/pages')) {
            Livewire::addNamespace('pages', $theme->path.'/resources/views/livewire/pages', $livewireNamespace.'\\Pages', $theme->path.'/app/Livewire/Pages', $theme->path.'/resources/views/livewire/pages');
        }

        if (File::isDirectory($theme->path.'/resources/views/layouts')) {
            Livewire::addNamespace('layouts', $theme->path.'/resources/views/layouts', $livewireNamespace.'\\Layouts', $theme->path.'/app/Livewire/Layouts', $theme->path.'/resources/views/layouts');
        }

        // 3. Register Global Alias Resolver
        static $resolverRegistered = false;
        if (!$resolverRegistered) {
            $this->registerThemeLivewireResolver();
            $resolverRegistered = true;
        }
    }

    /**
     * Register a dynamic Livewire component resolver for theme inheritance.
     */
    protected function registerThemeLivewireResolver(): void
    {
        Livewire::resolveMissingComponent(function (string $name) {
            if (!$this->activeTheme instanceof \AlizHarb\Themer\Theme) {
                return null;
            }

            /** @var array<string, bool> $isResolving */
            static $isResolving = [];
            if (isset($isResolving[$name])) {
                return null;
            }

            $isResolving[$name] = true;

            try {
                $isThemeNamespaced = str_contains($name, '::');
                $alias = $isThemeNamespaced ? strstr($name, '::', true) : null;
                $searchName = $isThemeNamespaced ? substr($name, strlen((string) $alias) + 2) : $name;

                if ($isThemeNamespaced && $this->themes->has((string) $alias)) {
                    $isResolving[$name] = false;
                    unset($isResolving[$name]);

                    return null;
                }

                $searchContexts = match ($alias) {
                    'pages' => ['pages.'],
                    'layouts' => ['layouts.'],
                    'theme' => [''],
                    default => $isThemeNamespaced ? null : ['', 'pages.', 'layouts.'],
                };

                if ($searchContexts === null) {
                    $isResolving[$name] = false;
                    unset($isResolving[$name]);

                    return null;
                }

                $themes = [$this->activeTheme];
                if ($this->activeTheme->parent && $this->themes->has($this->activeTheme->parent)) {
                    $parent = $this->themes->get($this->activeTheme->parent);
                    if ($parent instanceof Theme) {
                        $themes[] = $parent;
                    }
                }

                /** @var mixed $livewireFactory */
                $livewireFactory = app('livewire.factory');

                foreach ($themes as $theme) {
                    $themeAlias = strtolower($theme->name);

                    foreach ($searchContexts as $context) {
                        $targetPath = $themeAlias.'::'.$context.$searchName;

                        try {
                            $class = $livewireFactory->resolveComponentClass($targetPath);
                            if ($class) {
                                unset($isResolving[$name]);

                                return $class;
                            }
                        } catch (\Exception) {
                        }
                    }
                }

                if ($alias === 'pages' || $alias === 'layouts') {
                    $aliasesToCheck = [$alias];
                } elseif (!$isThemeNamespaced) {
                    $aliasesToCheck = ['pages', 'layouts'];
                } else {
                    $aliasesToCheck = [];
                }

                foreach ($aliasesToCheck as $currentAlias) {
                    $internalAlias = '__themer_app_'.$currentAlias;

                    static $internalRegistered = [];
                    if (!isset($internalRegistered[$currentAlias])) {
                        $appPath = $currentAlias === 'pages'
                            ? resource_path('views/livewire/pages')
                            : resource_path('views/layouts');

                        if (is_dir($appPath)) {
                            Livewire::addNamespace($internalAlias, $appPath);
                            $internalRegistered[$currentAlias] = true;
                        }
                    }

                    if (isset($internalRegistered[$currentAlias])) {
                        try {
                            $targetPath = $internalAlias.'::'.$searchName;
                            $class = $livewireFactory->resolveComponentClass($targetPath);
                            if ($class) {
                                unset($isResolving[$name]);

                                return $class;
                            }
                        } catch (\Exception) {
                        }
                    }
                }
            } finally {
                unset($isResolving[$name]);
            }

            return null;
        });
    }
}
