<?php

declare(strict_types=1);

namespace AlizHarb\Themer\Console\Commands;

use AlizHarb\Themer\Theme;
use AlizHarb\Themer\ThemeManager;
use Illuminate\Console\Command;

/**
 * Artisan command to list all discovered themes.
 */
final class ListThemesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'theme:list';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'List all discoverable themes in the application';

    /**
     * Execute the console command.
     */
    public function handle(ThemeManager $manager): int
    {
        /** @var \Illuminate\Support\Collection<string, Theme> $themes */
        $themes = $manager->all();
        $active = $manager->getActiveTheme();

        if ($themes->isEmpty()) {
            $this->components->warn('No themes discovered.');

            return self::SUCCESS;
        }

        /** @var array<int, array<int, string>> $rows */
        $rows = $themes->map(fn (Theme $theme): array => [
            $theme->name,
            $active?->name === $theme->name ? '<fg=green>Yes</>' : 'No',
            $theme->removable ? '<fg=green>Yes</>' : '<fg=red>No</>',
            $theme->disableable ? '<fg=green>Yes</>' : '<fg=red>No</>',
            $theme->path,
            $theme->parent ?? '<fg=gray>None</>',
        ])->values()->toArray();

        $this->table(['Name', 'Active', 'Removable', 'Disableable', 'Path', 'Parent'], $rows);

        return self::SUCCESS;
    }
}
