<?php

declare(strict_types=1);

namespace AlizHarb\Themer;

use AlizHarb\Themer\Console\Commands\ActivateThemeCommand;
use AlizHarb\Themer\Console\Commands\Laravel\ThemeComponentMakeCommand;
use AlizHarb\Themer\Console\Commands\Laravel\ThemeLivewireLayoutCommand;
use AlizHarb\Themer\Console\Commands\Laravel\ThemeLivewireMakeCommand;
use AlizHarb\Themer\Console\Commands\Laravel\ThemeViewMakeCommand;
use AlizHarb\Themer\Console\Commands\ListThemesCommand;
use AlizHarb\Themer\Console\Commands\MakeThemeCommand;
use AlizHarb\Themer\Console\Commands\PublishThemeAssetsCommand;
use AlizHarb\Themer\Console\Commands\ThemeBuildCommand;
use AlizHarb\Themer\Console\Commands\ThemeCacheCommand;
use AlizHarb\Themer\Console\Commands\ThemeCheckCommand;
use AlizHarb\Themer\Console\Commands\ThemeClearCommand;
use AlizHarb\Themer\Console\Commands\ThemeCloneCommand;
use AlizHarb\Themer\Console\Commands\ThemeDeleteCommand;
use AlizHarb\Themer\Console\Commands\ThemeDevCommand;
use AlizHarb\Themer\Console\Commands\ThemeInstallCommand;
use AlizHarb\Themer\Console\Commands\ThemeNpmCommand;
use AlizHarb\Themer\Console\Commands\ThemeUpgradeCommand;
use AlizHarb\Themer\Contracts\ThemerPlugin;
use AlizHarb\Themer\Plugins\ModulesPlugin;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Livewire\Features\SupportConsoleCommands\Commands\LayoutCommand as LivewireLayoutCommand;
use Livewire\Features\SupportConsoleCommands\Commands\MakeCommand as LivewireMakeCommand;

/**
 * The Service Provider for the Laravel Themer package.
 */
class ThemeServiceProvider extends ServiceProvider
{
    /**
     * Create a new service provider instance.
     */
    public function __construct(Application $app)
    {
        parent::__construct($app);
    }

    /**
     * The registered themer plugins.
     *
     * @var array<string, ThemerPlugin>
     */
    protected static array $plugins = [];

    /**
     * Register a themer plugin.
     */
    public static function registerPlugin(ThemerPlugin $plugin): void
    {
        self::$plugins[$plugin->getId()] = $plugin;
    }

    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/themer.php', 'themer');

        // Bind the provider instance so it can be resolved by dependent packages
        $this->app->instance(self::class, $this);

        $this->app->singleton(ThemeManager::class, function (): \AlizHarb\Themer\ThemeManager {
            return new ThemeManager();
        });

        $this->app->alias(ThemeManager::class, 'themer');

        /** @var \Illuminate\Routing\Router $router */
        $router = app('router');
        $router->aliasMiddleware('theme', Http\Middleware\SetTheme::class);

        // Register default plugins
        self::registerPlugin(new ModulesPlugin());

        $this->registerThemeCommands();
    }

    /**
     * Override standard Laravel/Livewire commands to be theme-aware.
     */
    protected function registerThemeCommands(): void
    {
        $this->app->extend(\Illuminate\Foundation\Console\ComponentMakeCommand::class, function (mixed $command, Application $app): object {
            return $app->make(ThemeComponentMakeCommand::class);
        });

        $this->app->extend(\Illuminate\Foundation\Console\ViewMakeCommand::class, function (mixed $command, Application $app): object {
            return $app->make(ThemeViewMakeCommand::class);
        });

        if (class_exists(LivewireMakeCommand::class)) {
            $commands = [
                LivewireMakeCommand::class => ThemeLivewireMakeCommand::class,
                LivewireLayoutCommand::class => ThemeLivewireLayoutCommand::class,
            ];

            foreach ($commands as $original => $themeCommand) {
                $this->app->extend($original, function (mixed $command, Application $app) use ($themeCommand): object {
                    // If the command is already overridden by Modular, don't break it
                    if ($command instanceof \AlizHarb\Modular\Livewire\Commands\ModularLivewireMakeCommand) {
                        return $command;
                    }

                    return $app->make($themeCommand);
                });
            }
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->bootForConsole();

        /** @var ThemeManager $manager */
        $manager = $this->app->make(ThemeManager::class);

        /** @var string $themesPath */
        $themesPath = config('themer.themes_path', base_path('themes'));

        $manager->scan($themesPath);

        /** @var string $active */
        $active = config('themer.active', 'default');

        if ($active !== '') {
            try {
                $manager->set($active);
            } catch (\Exception) {
                // Silently ignore if theme doesn't exist yet
            }
        }

        /** @var Collection<string, Theme> $themes */
        $themes = $manager->all();

        if ($manager->getActiveTheme() instanceof Theme) {
            $this->registerViteOverride();
        }

        foreach (self::$plugins as $plugin) {
            $plugin->register($this->app, $manager, $themes);
        }

        foreach (self::$plugins as $plugin) {
            $plugin->boot($this->app, $manager, $themes);
        }
    }

    /**
     * Bootstrap for the console environment.
     */
    protected function bootForConsole(): void
    {
        $this->publishes([
            __DIR__.'/../config/themer.php' => config_path('themer.php'),
        ], 'themer-config');

        $this->commands([
            ThemeInstallCommand::class,
            PublishThemeAssetsCommand::class,
            ListThemesCommand::class,
            ActivateThemeCommand::class,
            MakeThemeCommand::class,
            ThemeCacheCommand::class,
            ThemeClearCommand::class,
            ThemeCheckCommand::class,
            ThemeUpgradeCommand::class,
            ThemeNpmCommand::class,
            ThemeDevCommand::class,
            ThemeBuildCommand::class,
            ThemeDeleteCommand::class,
            ThemeCloneCommand::class,
        ]);
    }

    /**
     * Override the standard @vite directive to be theme-aware.
     */
    protected function registerViteOverride(): void
    {
        Blade::directive('vite', function (string $expression): string {
            return sprintf('<?php echo %s::vite(%s); ?>', \AlizHarb\Themer\ThemeAsset::class, $expression);
        });

        Blade::directive('theme_include', function (string $expression): string {
            return "<?php echo \$__env->make('theme::' . str_replace(['\'', '\"'], '', $expression), \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>";
        });
    }
}
