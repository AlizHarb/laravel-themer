<?php

declare(strict_types=1);

namespace AlizHarb\Themer\Console\Commands;

use AlizHarb\Themer\Theme;
use AlizHarb\Themer\ThemeManager;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;

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
        /** @var Collection<string, Theme> $themes */
        $themes = $manager->all();
        $active = $manager->getActiveTheme();

        if ($themes->isEmpty()) {
            $this->components->warn('No themes discovered.');

            return self::SUCCESS;
        }

        /** @var array<int, array<int, string>> $rows */
        $rows = $themes->sortBy('name')->map(fn (Theme $theme): array => [
            $theme->name,
            $theme->version,
            $theme->author ?: '<fg=gray>Unknown</>',
            $active?->slug === $theme->slug ? '<fg=green>Yes</>' : 'No',
            $theme->removable ? '<fg=green>Yes</>' : '<fg=red>No</>',
            $theme->disableable ? '<fg=green>Yes</>' : '<fg=red>No</>',
            $theme->parent ?? '<fg=gray>None</>',
        ])->values()->toArray();

        $this->table(['Name', 'Version', 'Author', 'Active', 'Removable', 'Disableable', 'Parent'], $rows);

        return self::SUCCESS;
    }
}
