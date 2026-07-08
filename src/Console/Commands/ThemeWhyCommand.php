<?php

declare(strict_types=1);

namespace AlizHarb\Themer\Console\Commands;

use AlizHarb\Themer\Theme;
use AlizHarb\Themer\ThemeManager;
use Illuminate\Console\Command;

final class ThemeWhyCommand extends Command
{
    protected $signature = 'theme:why {theme : Theme name or slug} {--json : Output machine-readable explanation}';

    protected $description = 'Explain why a theme exists and what it contributes';

    public function handle(ThemeManager $manager): int
    {
        $name = (string) $this->argument('theme');
        $theme = $manager->find($name);

        if (! $theme instanceof Theme) {
            $this->components->error("Theme [{$name}] not found.");

            return self::FAILURE;
        }

        $children = $manager->all()
            ->filter(fn (Theme $candidate): bool => $candidate->parent === $theme->slug || $candidate->parent === $theme->name)
            ->map(fn (Theme $candidate): string => $candidate->slug)
            ->values()
            ->all();

        $payload = [
            'theme' => $theme->slug,
            'active' => $manager->isActive($theme->slug),
            'parent' => $theme->parent,
            'children' => $children,
            'provides' => $theme->provides,
            'requires' => $theme->requires,
            'tokens' => array_keys($theme->tokens),
        ];

        if ($this->option('json')) {
            $this->line((string) json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

            return self::SUCCESS;
        }

        $this->components->info("Why theme [{$theme->slug}] exists");
        $this->components->twoColumnDetail('Active', $payload['active'] ? '<fg=green>Yes</>' : '<fg=gray>No</>');
        $this->components->twoColumnDetail('Parent', $theme->parent ?? '<fg=gray>None</>');
        $this->components->twoColumnDetail('Children', implode(', ', $children) ?: '<fg=gray>None</>');
        $this->components->twoColumnDetail('Provides', implode(', ', $theme->provides) ?: '<fg=gray>None</>');

        return self::SUCCESS;
    }
}
