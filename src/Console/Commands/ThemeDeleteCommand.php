<?php

declare(strict_types=1);

namespace AlizHarb\Themer\Console\Commands;

use AlizHarb\Themer\ThemeManager;
use AlizHarb\Themer\Traits\HasThemeOption;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Symfony\Component\Console\Input\InputOption;

/**
 * Console command to delete a theme.
 */
final class ThemeDeleteCommand extends Command
{
    use HasThemeOption;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $name = 'theme:delete';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete a theme and its assets';

    /**
     * Execute the console command.
     */
    public function handle(ThemeManager $manager): int
    {
        $themeName = (string) $this->getTheme();
        $theme = $manager->find($themeName);

        if (! $theme) {
            $this->components->error("Theme [{$themeName}] not found!");

            return self::FAILURE;
        }

        if (! $theme->removable && ! $this->option('force')) {
            $this->components->error("Theme [{$theme->name}] is marked as non-removable.");

            return self::FAILURE;
        }

        if (! $this->option('force') && ! $this->confirm("Are you sure you want to delete theme [{$theme->name}]? This action cannot be undone.", false)) {
            $this->components->info('Deletion cancelled.');

            return self::SUCCESS;
        }

        $this->components->info("Deleting theme [{$theme->name}]...");

        // Delete published assets
        /** @var string $assetFolder */
        $assetFolder = config('themer.assets.path', 'themes');
        $publicPath = public_path($assetFolder.'/'.$theme->slug);
        if (File::exists($publicPath)) {
            if (is_link($publicPath)) {
                File::delete($publicPath);
            } else {
                File::deleteDirectory($publicPath);
            }
            $this->line('  - Deleted published assets');
        }

        // Delete theme directory
        if (File::deleteDirectory($theme->path)) {
            $this->line('  - Deleted theme directory');

            // Clear cache
            $manager->reset();
            $this->call('theme:clear');

            $this->components->info("Theme [{$theme->name}] deleted successfully.");

            return self::SUCCESS;
        }

        $this->components->error("Failed to delete theme directory at [{$theme->path}].");

        return self::FAILURE;
    }

    /**
     * Get the console command options.
     *
     * @return array<int, array{0: string, 1: string|null, 2: int, 3: string, 4: mixed|null}>
     */
    protected function getOptions(): array
    {
        /** @var array<int, array{0: string, 1: string|null, 2: int, 3: string, 4: mixed|null}> $options */
        $options = array_merge([
            ['theme', null, InputOption::VALUE_REQUIRED, 'The name or slug of the theme to delete', null],
            ['force', null, InputOption::VALUE_NONE, 'Delete even if removable is false', null],
        ], parent::getOptions());

        return $options;
    }
}
