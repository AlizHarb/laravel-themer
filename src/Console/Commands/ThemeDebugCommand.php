<?php

declare(strict_types=1);

namespace AlizHarb\Themer\Console\Commands;

use AlizHarb\Themer\Theme;
use AlizHarb\Themer\ThemeManager;
use Illuminate\Console\Command;

final class ThemeDebugCommand extends Command
{
    protected $signature = 'theme:debug {theme : Theme name or slug} {--json : Output machine-readable details}';

    protected $description = 'Debug a theme manifest, resources, inheritance, and tokens';

    public function handle(ThemeManager $manager): int
    {
        $name = (string) $this->argument('theme');
        $theme = $manager->find($name);

        if (! $theme instanceof Theme) {
            $this->components->error("Theme [{$name}] not found.");

            return self::FAILURE;
        }

        $payload = [
            'theme' => $theme->toArray(),
            'inheritance' => $manager->getInheritanceChain($theme)->map(fn (Theme $parent): string => $parent->slug)->values()->all(),
            'cache' => [
                'stale' => $manager->cacheIsStale(),
                'meta' => $manager->getCacheMeta(),
            ],
        ];

        if ($this->option('json')) {
            $this->line((string) json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

            return self::SUCCESS;
        }

        $this->components->info("Theme Debug: {$theme->name}");
        $this->components->twoColumnDetail('Slug', $theme->slug);
        $this->components->twoColumnDetail('Version', $theme->version);
        $this->components->twoColumnDetail('Parent', $theme->parent ?? '<fg=gray>None</>');
        $this->components->twoColumnDetail('Provides', implode(', ', $theme->provides) ?: '<fg=gray>None</>');
        $this->components->twoColumnDetail('Tokens', (string) count($theme->tokens));

        return self::SUCCESS;
    }
}
