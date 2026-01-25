<?php

declare(strict_types=1);

namespace AlizHarb\Themer\Console\Commands;

use AlizHarb\Themer\Theme;
use AlizHarb\Themer\ThemeManager;
use Illuminate\Console\Command;

/**
 * Artisan command to publish or symlink theme assets to the public directory.
 */
final class PublishThemeAssetsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'theme:publish {theme? : The name of the theme to publish assets for}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Publish or symlink theme assets to the public directory';

    /**
     * Execute the console command.
     */
    public function handle(ThemeManager $manager): int
    {
        /** @var string|null $themeName */
        $themeName = $this->argument('theme');

        if ($themeName) {
            $theme = $manager->all()->get($themeName);

            if (!$theme instanceof Theme) {
                $this->components->error(sprintf('Theme [%s] not found.', $themeName));

                return self::FAILURE;
            }

            $themes = collect([$theme]);
        } else {
            /** @var \Illuminate\Support\Collection<string, Theme> $themes */
            $themes = $manager->all();
        }

        if ($themes->isEmpty()) {
            $this->components->warn('No themes discovered.');

            return self::SUCCESS;
        }

        foreach ($themes as $theme) {
            $this->components->task(sprintf('Publishing assets for [%s]', $theme->name), function () use ($manager, $theme): void {
                $manager->publishAssets($theme);
            });
        }

        return self::SUCCESS;
    }
}
