<?php

declare(strict_types=1);

namespace AlizHarb\Themer\Console\Commands;

use AlizHarb\Themer\Theme;
use AlizHarb\Themer\ThemeManager;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

/**
 * Artisan command to display detailed information about a specific theme.
 */
final class ThemeInfoCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'theme:info {theme? : The name or slug of the theme}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Display detailed information about a specific theme';

    /**
     * Execute the console command.
     */
    public function handle(ThemeManager $manager): int
    {
        $themeName = $this->argument('theme') ?: $manager->getActiveTheme()?->name;

        if (! $themeName) {
            $this->components->error('Please specify a theme or activate one first.');

            return self::FAILURE;
        }

        $theme = $manager->find((string) $themeName);

        if (! $theme) {
            $this->components->error("Theme [{$themeName}] not found.");

            return self::FAILURE;
        }

        $this->displayBasicInfo($theme);
        $this->displayResources($theme);
        $this->displayInheritance($theme, $manager);
        $this->displayAssets($theme);

        return self::SUCCESS;
    }

    private function displayBasicInfo(Theme $theme): void
    {
        $this->components->twoColumnDetail('Name', $theme->name);
        $this->components->twoColumnDetail('Slug', $theme->slug);
        $this->components->twoColumnDetail('Version', $theme->version);
        $this->components->twoColumnDetail('Author', $theme->author ?: '<fg=gray>Unknown</>');
        $this->components->twoColumnDetail('Path', $theme->path);
    }

    private function displayResources(Theme $theme): void
    {
        $this->newLine();
        $this->components->info('Resources');

        $this->components->twoColumnDetail('Views', $theme->hasViews ? '<fg=green>Found</>' : '<fg=yellow>None</>');
        $this->components->twoColumnDetail('Translations', $theme->hasTranslations ? '<fg=green>Found</>' : '<fg=yellow>None</>');
        $this->components->twoColumnDetail('Service Provider', $theme->hasProvider ? '<fg=green>Found</>' : '<fg=yellow>None</>');
        $this->components->twoColumnDetail('Livewire Components', $theme->hasLivewire ? '<fg=green>Found</>' : '<fg=yellow>None</>');
    }

    private function displayInheritance(Theme $theme, ThemeManager $manager): void
    {
        $this->newLine();
        $this->components->info('Inheritance');

        if (! $theme->parent) {
            $this->components->twoColumnDetail('Parent', '<fg=gray>None (Base Theme)</>');

            return;
        }

        $chain = $manager->getInheritanceChain($theme);

        $this->components->twoColumnDetail('Immediate Parent', $theme->parent);
        $this->components->twoColumnDetail('Inheritance Chain', $chain->map(fn (Theme $p) => $p->name)->prepend($theme->name)->implode(' -> '));
    }

    private function displayAssets(Theme $theme): void
    {
        $this->newLine();
        $this->components->info('Assets & Vite');

        $this->components->twoColumnDetail('Custom Asset Path', $theme->assetPath ?: '<fg=gray>Default (themes/'.$theme->name.')</>');
        $this->components->twoColumnDetail('Vite Config', File::exists($theme->path.'/vite.config.js') ? '<fg=green>Found</>' : '<fg=yellow>Missing</>');
        $this->components->twoColumnDetail('Package.json', File::exists($theme->path.'/package.json') ? '<fg=green>Found</>' : '<fg=yellow>Missing</>');
    }
}
