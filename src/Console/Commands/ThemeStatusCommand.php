<?php

declare(strict_types=1);

namespace AlizHarb\Themer\Console\Commands;

use AlizHarb\Themer\Support\ThemeDiagnostics;
use AlizHarb\Themer\Theme;
use AlizHarb\Themer\ThemeManager;
use Illuminate\Console\Command;

final class ThemeStatusCommand extends Command
{
    protected $signature = 'theme:status {--json : Output machine-readable status}';

    protected $description = 'Display high-level Laravel Themer status';

    public function handle(ThemeManager $manager, ThemeDiagnostics $diagnostics): int
    {
        $report = $diagnostics->report($manager);
        $active = $manager->getActiveTheme();
        $payload = [
            'active' => $active?->slug,
            'status' => $report['status'],
            'summary' => $report['summary'],
            'cache' => [
                'path' => $manager->getCachePath(),
                'meta' => $manager->getCacheMeta(),
            ],
        ];

        if ($this->option('json')) {
            $this->line((string) json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

            return self::SUCCESS;
        }

        $this->components->info('Laravel Themer Status');
        $this->components->twoColumnDetail('Active Theme', $active instanceof Theme ? $active->slug : '<fg=gray>None</>');
        $this->components->twoColumnDetail('Themes', (string) $report['summary']['themes']);
        $this->components->twoColumnDetail('Issues', (string) $report['summary']['issues']);
        $this->components->twoColumnDetail('Cache Stale', $report['summary']['cache_stale'] ? '<fg=yellow>Yes</>' : '<fg=green>No</>');

        return self::SUCCESS;
    }
}
