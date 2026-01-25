<?php

declare(strict_types=1);

namespace AlizHarb\Themer\Console\Commands\Laravel;

use AlizHarb\Themer\Traits\HasThemeOption;
use Illuminate\Support\Facades\Config;
use Livewire\Features\SupportConsoleCommands\Commands\LayoutCommand;

/**
 * Custom Livewire LayoutCommand that supports themes.
 */
final class ThemeLivewireLayoutCommand extends LayoutCommand
{
    use HasThemeOption;

    /**
     * The console command signature.
     *
     * @var string
     */
    protected $signature = 'livewire:layout {--force} {--stub= : If you have several stubs, stored in subfolders } {--theme= : The name of the theme }';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $themeName = $this->getTheme();

        if ($themeName) {
            /** @var \AlizHarb\Themer\ThemeManager $manager */
            $manager = app(\AlizHarb\Themer\ThemeManager::class);
            $theme = $manager->all()->get($themeName);

            if (!$theme) {
                $this->components->error(sprintf('Theme [%s] not found.', $themeName));

                return self::FAILURE;
            }

            // Redirect LayoutCommand by temporarily overriding Livewire configuration.
            $originalLayout = (string) config('livewire.component_layout');
            $originalNamespace = (string) config('livewire.component_namespaces.theme');

            Config::set('livewire.component_namespaces.theme', $theme->path.'/resources/views');
            Config::set('livewire.component_layout', 'theme::layouts.app');

            try {
                $result = (int) parent::handle();
            } finally {
                Config::set('livewire.component_layout', $originalLayout);
                Config::set('livewire.component_namespaces.theme', $originalNamespace);
            }

            return $result;
        }

        return (int) parent::handle();
    }
}
