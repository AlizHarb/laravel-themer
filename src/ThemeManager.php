<?php

declare(strict_types=1);

namespace AlizHarb\Themer;

use AlizHarb\Themer\Exceptions\ThemeNotFoundException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Blade;
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
     * Reset the theme manager state.
     */
    public function reset(): void
    {
        $this->themes = new Collection();
        $this->activeTheme = null;
    }

    public function register(Theme $theme): void
    {
        $this->themes->put($theme->name, $theme);

        // Also register by slug and folder name for flexible lookup
        $this->themes->put($theme->slug, $theme);
        $this->themes->put(basename($theme->path), $theme);
    }

    /**
     * Scan the given directory for themes.
     */
    public function scan(string $path): void
    {
        $cachePath = $this->getCachePath();

        if (! app()->runningUnitTests() && file_exists($cachePath)) {
            /** @var array<int, array{
             *     name: string,
             *     slug: string,
             *     path: string,
             *     assetPath: string,
             *     parent: string|null,
             *     config: array<string, mixed>,
             *     version: string,
             *     author: string|null,
             *     authors: array<int, array{name: string, email?: string, role?: string}>,
             *     hasViews: bool,
             *     hasTranslations: bool,
             *     hasProvider: bool,
             *     hasLivewire: bool,
             *     removable: bool,
             *     disableable: bool,
             *     screenshots: array<int, string>,
             *     tags: array<int, string>
             * }> $themes */
            $themes = require $cachePath;
            foreach ($themes as $data) {
                $this->register(new Theme(
                    $data['name'],
                    $data['slug'],
                    $data['path'],
                    $data['assetPath'],
                    $data['parent'],
                    $data['config'],
                    $data['version'],
                    $data['author'],
                    $data['authors'],
                    $data['hasViews'],
                    $data['hasTranslations'],
                    $data['hasProvider'],
                    $data['hasLivewire'],
                    $data['removable'],
                    $data['disableable'],
                    $data['screenshots'],
                    $data['tags']
                ));
            }

            return;
        }
        if (! File::isDirectory($path)) {
            return;
        }

        $directories = File::directories($path);

        foreach ($directories as $directory) {
            $themeJsonPath = $directory.'/theme.json';

            if (File::exists($themeJsonPath)) {
                $json = File::get($themeJsonPath);
                /** @var array<string, mixed>|null $config */
                $config = json_decode($json, true);

                if (! is_array($config)) {
                    continue;
                }

                $name = (string) ($config['name'] ?? basename((string) $directory));
                $slug = (string) ($config['slug'] ?? Str::slug($name)); // Read slug or generate from name
                $assetPath = (string) ($config['asset_path'] ?? '');
                $parent = $config['parent'] ?? null;
                $version = (string) ($config['version'] ?? '1.0.0');
                $author = $config['author'] ?? null;
                /** @var array<int, array{name: string, email?: string, role?: string}> $authors */
                $authors = $config['authors'] ?? [];

                // Check for slug uniqueness
                if ($this->themes->has($slug)) {
                    throw new \RuntimeException("Theme slug '{$slug}' is already in use by theme '{$this->themes->get($slug)->name}'. Each theme must have a unique slug.");
                }

                $theme = new Theme(
                    name: $name,
                    slug: $slug,
                    path: (string) $directory,
                    assetPath: $assetPath,
                    parent: $parent,
                    config: $config,
                    version: $version,
                    author: $author,
                    authors: $authors,
                    hasViews: is_dir($directory.'/resources/views'),
                    hasTranslations: is_dir($directory.'/resources/lang') || is_dir($directory.'/lang'),
                    hasProvider: file_exists($directory.'/ThemeServiceProvider.php'),
                    hasLivewire: is_dir($directory.'/app/Livewire') || is_dir($directory.'/resources/views/livewire'),
                    removable: (bool) ($config['removable'] ?? true),
                    disableable: (bool) ($config['disableable'] ?? true),
                    screenshots: (array) ($config['screenshots'] ?? []),
                    tags: (array) ($config['tags'] ?? [])
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
     * Set up a theme-aware generator context (config overrides, namespaces, directories).
     *
     *
     * @throws ThemeNotFoundException
     */
    public function useThemeGenerator(string $themeName, callable $callback): mixed
    {
        $theme = $this->themes->get($themeName);

        if (! ($theme instanceof Theme)) {
            throw ThemeNotFoundException::make($themeName);
        }

        $themeLower = $theme->slug; // Use the theme's slug for Livewire namespace
        $classPath = $theme->path.'/app/Livewire';
        $viewPath = $theme->path.'/resources/views/livewire';

        if (! is_dir($classPath)) {
            mkdir($classPath, 0755, true);
        }

        if (! is_dir($viewPath)) {
            mkdir($viewPath, 0755, true);
        }

        $originalNamespace = (string) config('livewire.class_namespace');
        $originalViewPath = (string) config('livewire.view_path');

        \Livewire\Livewire::addNamespace(
            $themeLower,
            $viewPath,
            'Theme\\'.Str::studly($theme->name).'\\Livewire',
            $classPath,
            $viewPath
        );

        \Illuminate\Support\Facades\Config::set('livewire.class_namespace', 'Theme\\'.Str::studly($theme->name).'\\Livewire');
        \Illuminate\Support\Facades\Config::set('livewire.view_path', $viewPath);

        // Livewire 4 redirection
        \Illuminate\Support\Facades\Config::set('livewire.component_locations', [$viewPath]);
        \Illuminate\Support\Facades\Config::set('livewire.component_namespaces', [
            'theme' => $viewPath,
        ]);

        try {
            return $callback($theme);
        } finally {
            \Illuminate\Support\Facades\Config::set('livewire.class_namespace', $originalNamespace);
            \Illuminate\Support\Facades\Config::set('livewire.view_path', $originalViewPath);
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

        if (! ($theme instanceof Theme)) {
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
     * Register all theme resources (Views, Languages, Providers, Livewire, etc.).
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
        if (! $theme->hasProvider) {
            return;
        }

        $providerPath = $theme->path.'/ThemeServiceProvider.php';

        require_once $providerPath;

        $studlyName = \Illuminate\Support\Str::studly($theme->name);
        $namespacedClass = "Theme\\{$studlyName}\\ThemeServiceProvider";

        if (class_exists($namespacedClass)) {
            app()->register($namespacedClass);
        } elseif (class_exists('ThemeServiceProvider')) {
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

        // 2. Register Auto-Namespaces from config
        $autoNamespaces = config('themer.auto_namespaces', []);

        foreach ($autoNamespaces as $namespace => $relativePath) {
            $nsPaths = collect($paths)
                ->map(fn (string $p): string => $p.'/'.str_replace('resources/views/', '', $relativePath))
                ->filter(fn (string $p) => $this->directoryExists($p))
                ->toArray();

            if (! empty($nsPaths)) {
                app('view')->addNamespace($namespace, $nsPaths);

                foreach ($nsPaths as $path) {
                    \Illuminate\Support\Facades\Blade::anonymousComponentPath($path, $namespace);
                }
            }
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
        if (! $theme->hasTranslations) {
            return;
        }

        $langPath = $theme->path.'/resources/lang';

        if (! $this->directoryExists($langPath)) {
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
     * Check if a specific theme is currently active.
     */
    public function isActive(string $themeName): bool
    {
        if (! $this->activeTheme) {
            return false;
        }

        return $this->activeTheme->name === $themeName || $this->activeTheme->slug === $themeName;
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
        $seen = [$theme->slug => true];

        while ($current->parent && $this->themes->has($current->parent)) {
            $parent = $this->themes->get($current->parent);

            if (! ($parent instanceof Theme)) {
                break;
            }

            // Loop Guard: Prevent infinite recursion if circular dependency exists
            if (isset($seen[$parent->slug])) {
                break;
            }

            $seen[$parent->slug] = true;
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
        $themeAssetsPath = $theme->path.DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR.'assets';
        if (! File::isDirectory($themeAssetsPath)) {
            $themeAssetsPath = $theme->path.DIRECTORY_SEPARATOR.'assets';
        }

        if (! File::isDirectory($themeAssetsPath)) {
            return;
        }

        /** @var string $assetSubPath */
        $assetSubPath = config('themer.assets.path', 'themes');
        $publicPath = public_path($assetSubPath.DIRECTORY_SEPARATOR.$theme->name);

        // Performance Optimization: Check if symlink already exists and points to the right place
        if (config('themer.assets.symlink', true) && function_exists('symlink')) {
            if (is_link($publicPath) && readlink($publicPath) === $themeAssetsPath) {
                return;
            }
        }

        if (! File::isDirectory(dirname($publicPath))) {
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
            // Only copy if files changed or directory missing
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
        if (! $this->activeTheme instanceof \AlizHarb\Themer\Theme) {
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
        return $this->themes->unique('name');
    }

    /**
     * Find a theme by name, slug, or directory name.
     */
    public function find(string $themeName): ?Theme
    {
        return $this->themes->get($themeName);
    }

    /**
     * Register theme-specific Livewire support.
     */
    protected function registerThemeLivewire(Theme $theme): void
    {
        if (! class_exists(Livewire::class)) {
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

        // 2. Register Config-Based Alias Baseline
        $autoNamespaces = config('themer.auto_namespaces', []);
        foreach ($autoNamespaces as $alias => $relativePath) {
            $fullPath = $theme->path.'/'.$relativePath;
            if (File::isDirectory($fullPath)) {
                $aliasNamespace = $livewireNamespace.'\\'.Str::studly($alias);
                Livewire::addNamespace($alias, $fullPath, $aliasNamespace, $theme->path.'/app/Livewire/'.Str::studly($alias), $fullPath);
            }
        }

        // 3. Register Global Alias Resolver
        static $resolverRegistered = false;
        if (! $resolverRegistered) {
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
            if (! $this->activeTheme instanceof \AlizHarb\Themer\Theme) {
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

                $themes = array_merge([$this->activeTheme], $this->getThemeParents($this->activeTheme));

                /** @var \Livewire\Factory\Factory $livewireFactory */
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
                } elseif (! $isThemeNamespaced) {
                    $aliasesToCheck = ['pages', 'layouts'];
                } else {
                    $aliasesToCheck = [];
                }

                foreach ($aliasesToCheck as $currentAlias) {
                    $internalAlias = '__themer_app_'.$currentAlias;

                    /** @var array<string, bool> $internalRegistered */
                    static $internalRegistered = [];
                    if (! isset($internalRegistered[$currentAlias])) {
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
                            /** @var \Livewire\Factory\Factory $livewireFactory */
                            $livewireFactory = app('livewire.factory');
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
        static $cache = [];

        if (! isset($cache[$path])) {
            $cache[$path] = File::isDirectory($path);
        }

        return $cache[$path];
    }
}
