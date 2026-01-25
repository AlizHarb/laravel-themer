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
            /** @var array<int, array{
             *     name: string,
             *     path: string,
             *     assetPath?: string,
             *     parent?: string,
             *     config?: array<string, mixed>,
             *     version?: string,
             *     hasViews?: bool,
             *     hasTranslations?: bool,
             *     hasProvider?: bool,
             *     hasLivewire?: bool
             * }> $themes */
            $themes = require $cachePath;
            foreach ($themes as $data) {
                $this->register(new Theme(
                    $data['name'],
                    $data['path'],
                    $data['assetPath'] ?? '',
                    $data['parent'] ?? null,
                    $data['config'] ?? [],
                    $data['version'] ?? '1.0.0',
                    $data['hasViews'] ?? false,
                    $data['hasTranslations'] ?? false,
                    $data['hasProvider'] ?? false,
                    $data['hasLivewire'] ?? false
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
                /** @var array<string, mixed>|null $config */
                $config = json_decode($json, true);

                if (!is_array($config)) {
                    continue;
                }

                $name = (string) ($config['name'] ?? basename((string) $directory));
                $assetPath = (string) ($config['asset_path'] ?? '');
                $parent = $config['parent'] ?? null;
                $version = (string) ($config['version'] ?? '1.0.0');

                $theme = new Theme(
                    name: $name,
                    path: (string) $directory,
                    assetPath: $assetPath,
                    parent: $parent,
                    config: $config,
                    version: $version,
                    hasViews: is_dir($directory.'/resources/views'),
                    hasTranslations: is_dir($directory.'/resources/lang') || is_dir($directory.'/lang'),
                    hasProvider: file_exists($directory.'/ThemeServiceProvider.php'),
                    hasLivewire: is_dir($directory.'/app/Livewire') || is_dir($directory.'/resources/views/livewire')
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
     * Temporarily switch to a specific theme for the duration of a callback.
     */
    public function forTheme(string $themeName, \Closure $callback): mixed
    {
        $originalTheme = $this->activeTheme;

        try {
            $this->set($themeName);

            return $callback($this);
        } finally {
            if ($originalTheme) {
                $this->set($originalTheme->name);
            }
        }
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
     * Register theme Vite configuration.
     */
    protected function registerThemeVite(Theme $theme): void
    {
        // Placeholder for advanced Vite integration
    }

    /**
     * Register all theme resources (Views, Languages, Providers, Livewire, etc.)
     */
    protected function registerResources(Theme $theme): void
    {
        $this->registerThemeViews($theme);
        $this->registerThemeLanguages($theme);
        $this->registerThemeServiceProvider($theme);

        foreach ($this->getThemeParents($theme) as $parent) {
            $this->registerThemeLivewire($parent);
        }

        $this->registerThemeLivewire($theme);
        $this->registerThemeVite($theme);

        if (config('themer.assets.publish_on_activate', true)) {
            $this->publishAssets($theme);
        }
    }

    protected function registerThemeServiceProvider(Theme $theme): void
    {
        if (!$theme->hasProvider) {
            return;
        }

        $providerPath = $theme->path.'/ThemeServiceProvider.php';

        require_once $providerPath;

        if (class_exists('ThemeServiceProvider')) {
            app()->register('ThemeServiceProvider');
        }
    }

    /**
     * Register theme view namespaces and locations.
     */
    protected function registerThemeViews(Theme $theme): void
    {
        $paths = [];

        if ($theme->hasViews) {
            $paths[] = $theme->path.'/resources/views';
        }

        foreach ($this->getThemeParents($theme) as $parent) {
            if ($parent->hasViews) {
                $paths[] = $parent->path.'/resources/views';
            }
        }

        if (empty($paths)) {
            return;
        }

        // 1. Register 'theme::' namespace
        app('view')->addNamespace('theme', $paths);

        // 2. Register 'layouts::' and 'pages::' namespaces if they exist
        $layoutPaths = collect($paths)
            ->map(fn (string $p): string => $p.'/layouts')
            ->filter(fn (string $p) => $this->directoryExists($p))
            ->toArray();

        if (!empty($layoutPaths)) {
            app('view')->addNamespace('layouts', $layoutPaths);
        }

        $pagesPaths = collect($paths)
            ->map(fn (string $p): string => $p.'/livewire/pages')
            ->filter(fn (string $p) => $this->directoryExists($p))
            ->toArray();

        if (!empty($pagesPaths)) {
            app('view')->addNamespace('pages', $pagesPaths);
        }

        // 3. Register for dynamic resolution (Active -> Parent -> App)
        /** @var \Illuminate\View\FileViewFinder $finder */
        $finder = app('view')->getFinder();

        foreach (array_reverse($paths) as $path) {
            $finder->prependLocation($path);

            // Register Blade Components directory if it exists
            $componentPath = $path.'/components';
            if ($this->directoryExists($componentPath)) {
                app('view')->addNamespace('theme-components', $componentPath);
            }
        }
    }

    protected function registerThemeLanguages(Theme $theme): void
    {
        if (!$theme->hasTranslations) {
            return;
        }

        $langPath = $theme->path.'/resources/lang';

        if (!$this->directoryExists($langPath)) {
            $langPath = $theme->path.'/lang';
        }

        /** @var \Illuminate\Translation\Translator $translator */
        $translator = app()->make('translator');
        $lowerName = strtolower($theme->name);

        $translator->addNamespace($lowerName, $langPath);

        if ($theme === $this->activeTheme) {
            $translator->addNamespace('theme', $langPath);
        }

        $translator->addJsonPath($langPath);

        foreach ($this->getThemeParents($theme) as $parent) {
            $this->registerThemeLanguages($parent);
        }
    }

    /**
     * Get the parents of a theme.
     *
     * @return array<int, Theme>
     */
    protected function getThemeParents(Theme $theme): array
    {
        $parents = [];
        $current = $theme;

        while ($current->parent && $this->themes->has($current->parent)) {
            $parent = $this->themes->get($current->parent);
            if (!($parent instanceof Theme)) {
                break;
            }
            $parents[] = $parent;
            $current = $parent;
        }

        return $parents;
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

        foreach ($this->getThemeParents($this->activeTheme) as $parent) {
            if ($parent->hasViews) {
                $paths[] = $parent->path.'/resources/views';
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

                        if ($this->directoryExists($appPath)) {
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

    /**
     * Check if a directory exists (optimized for cache).
     */
    protected function directoryExists(string $path): bool
    {
        return app()->isProduction() ? true : is_dir($path);
    }
}
