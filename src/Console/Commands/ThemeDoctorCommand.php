<?php

declare(strict_types=1);

namespace AlizHarb\Themer\Console\Commands;

use AlizHarb\Themer\Support\ThemeDiagnostics;
use AlizHarb\Themer\ThemeManager;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

final class ThemeDoctorCommand extends Command
{
    protected $signature = 'theme:doctor {--json : Output machine-readable diagnostics} {--fix : Apply safe repairs}';

    protected $description = 'Diagnose Laravel Themer installation and theme health';

    public function handle(ThemeManager $manager, ThemeDiagnostics $diagnostics): int
    {
        if ($this->option('fix')) {
            $this->applySafeFixes($manager);
        }

        $report = $diagnostics->report($manager);

        if ($this->option('json')) {
            $this->line((string) json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

            return $report['status'] === 'error' ? self::FAILURE : self::SUCCESS;
        }

        $this->components->info('Laravel Themer Doctor');
        $this->components->twoColumnDetail('Themes', (string) $report['summary']['themes']);
        $this->components->twoColumnDetail('Status', strtoupper($report['status']));

        foreach ($report['issues'] as $issue) {
            $message = ($issue['theme'] ?? null) ? "[{$issue['theme']}] {$issue['message']}" : $issue['message'];
            $issue['level'] === 'error' ? $this->components->error($message) : $this->components->warn($message);
        }

        if ($report['issues'] === []) {
            $this->components->info('No theme issues detected.');
        }

        return $report['status'] === 'error' ? self::FAILURE : self::SUCCESS;
    }

    private function applySafeFixes(ThemeManager $manager): void
    {
        /** @var string $themesPath */
        $themesPath = config('themer.themes_path', base_path('themes'));

        if (! File::isDirectory($themesPath)) {
            File::makeDirectory($themesPath, 0755, true);
            $this->components->info('Created missing themes directory.');
        }

        if ($manager->cacheIsStale()) {
            $this->call('theme:refresh');
        }
    }
}
