<?php

declare(strict_types=1);

namespace AlizHarb\Themer\Console\Commands;

use AlizHarb\Themer\Theme;
use AlizHarb\Themer\ThemeManager;
use Illuminate\Console\Command;

final class ThemeGraphCommand extends Command
{
    protected $signature = 'theme:graph {--format=ascii : Output format: ascii, json, dot}';

    protected $description = 'Render the theme inheritance graph';

    public function handle(ThemeManager $manager): int
    {
        $format = (string) $this->option('format');
        $themes = $manager->all()->values();
        $edges = $themes
            ->filter(fn (Theme $theme): bool => $theme->parent !== null)
            ->map(fn (Theme $theme): array => ['from' => $theme->slug, 'to' => $theme->parent])
            ->values()
            ->all();

        if ($format === 'json') {
            $this->line((string) json_encode(['themes' => $themes->map->slug->all(), 'edges' => $edges], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

            return self::SUCCESS;
        }

        if ($format === 'dot') {
            $this->line('digraph themes {');
            foreach ($edges as $edge) {
                $this->line(sprintf('  "%s" -> "%s";', $edge['from'], $edge['to']));
            }
            $this->line('}');

            return self::SUCCESS;
        }

        foreach ($themes as $theme) {
            $this->line($theme->parent ? "{$theme->slug} -> {$theme->parent}" : $theme->slug);
        }

        return self::SUCCESS;
    }
}
