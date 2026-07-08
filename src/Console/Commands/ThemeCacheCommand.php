<?php

declare(strict_types=1);

namespace AlizHarb\Themer\Console\Commands;

use AlizHarb\Themer\Events\ThemeCached;
use AlizHarb\Themer\Events\ThemeCaching;
use AlizHarb\Themer\ThemeManager;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class ThemeCacheCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'theme:cache';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Discover themes and cache them for zero-IO production performance';

    /**
     * Execute the console command.
     */
    public function handle(ThemeManager $manager): int
    {
        $this->components->info('Compiling Laravel Themer Manifest...');

        ThemeCaching::dispatch();

        $this->call('theme:clear');

        try {
            $themesPath = config('themer.themes_path', base_path('themes'));
            $themes = $manager->scan((string) $themesPath);

            if ($themes->isEmpty()) {
                $this->components->warn('No themes discovered to cache.');

                return self::SUCCESS;
            }

            $cachePath = $manager->getCachePath();
            $cachePayload = [];

            foreach ($themes as $slug => $theme) {
                $themeArray = $theme->toArray();
                $themeArray['config']['_inheritance_chain'] = $manager->getInheritanceChain((string) $slug)
                    ->map(fn ($t) => $t->toArray())
                    ->all();
                $cachePayload[$slug] = $themeArray;
            }

            $payload = [
                'meta' => $manager->buildCacheMeta(),
                'themes' => $cachePayload,
            ];

            $content = '<?php return '.var_export($payload, true).';';

            File::put($cachePath, $content);

            $this->components->info(sprintf('Successfully cached %d themes into %s.', $themes->count(), 'bootstrap/cache/themes.php'));
            ThemeCached::dispatch($themes->count());

            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->components->error('Failed to cache themes: '.$e->getMessage());

            return self::FAILURE;
        }
    }
}
