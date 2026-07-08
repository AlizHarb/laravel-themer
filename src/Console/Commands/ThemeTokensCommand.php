<?php

declare(strict_types=1);

namespace AlizHarb\Themer\Console\Commands;

use AlizHarb\Themer\Theme;
use AlizHarb\Themer\ThemeManager;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

final class ThemeTokensCommand extends Command
{
    protected $signature = 'theme:tokens {theme? : Theme name or slug} {--json : Output tokens as JSON} {--css : Output tokens as CSS variables}';

    protected $description = 'Display design tokens for a theme';

    public function handle(ThemeManager $manager): int
    {
        $name = $this->argument('theme') ?: $manager->getActiveTheme()?->slug;

        if (! $name) {
            $this->components->error('Please specify a theme or activate one first.');

            return self::FAILURE;
        }

        $theme = $manager->find((string) $name);

        if (! $theme instanceof Theme) {
            $this->components->error("Theme [{$name}] not found.");

            return self::FAILURE;
        }

        if ($this->option('json')) {
            $this->line((string) json_encode($theme->tokens, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

            return self::SUCCESS;
        }

        if ($this->option('css')) {
            $this->line(':root {');
            foreach ($theme->tokens as $key => $value) {
                $this->line(sprintf('  --theme-%s: %s;', Str::of((string) $key)->replace('.', '-')->kebab(), $this->stringValue($value)));
            }
            $this->line('}');

            return self::SUCCESS;
        }

        foreach ($theme->tokens as $key => $value) {
            $this->components->twoColumnDetail((string) $key, $this->stringValue($value));
        }

        if ($theme->tokens === []) {
            $this->components->warn('No design tokens defined.');
        }

        return self::SUCCESS;
    }

    private function stringValue(mixed $value): string
    {
        return is_bool($value) ? ($value ? 'true' : 'false') : (string) $value;
    }
}
