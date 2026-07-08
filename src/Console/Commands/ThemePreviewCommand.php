<?php

declare(strict_types=1);

namespace AlizHarb\Themer\Console\Commands;

use AlizHarb\Themer\Events\ThemePreviewed;
use AlizHarb\Themer\Events\ThemePreviewing;
use AlizHarb\Themer\Theme;
use AlizHarb\Themer\ThemeManager;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\URL;

final class ThemePreviewCommand extends Command
{
    protected $signature = 'theme:preview {theme : Theme name or slug} {--path=/ : URL path to preview} {--expires=60 : Signed URL lifetime in minutes} {--signed : Generate a signed preview URL}';

    protected $description = 'Generate a safe preview URL for an inactive theme';

    public function handle(ThemeManager $manager): int
    {
        $name = (string) $this->argument('theme');
        $theme = $manager->find($name);

        if (! $theme instanceof Theme) {
            $this->components->error("Theme [{$name}] not found.");

            return self::FAILURE;
        }

        ThemePreviewing::dispatch($theme->slug);

        $path = '/'.ltrim((string) $this->option('path'), '/');
        $query = ['preview_theme' => $theme->slug];

        $url = $this->option('signed')
            ? URL::temporarySignedRoute('themer.preview', now()->addMinutes((int) $this->option('expires')), $query + ['path' => ltrim($path, '/')])
            : url($path).'?'.http_build_query($query);

        ThemePreviewed::dispatch($theme->slug, $url);

        $this->components->info('Preview URL');
        $this->line($url);

        return self::SUCCESS;
    }
}
