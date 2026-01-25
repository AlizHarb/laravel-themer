<?php

declare(strict_types=1);

namespace AlizHarb\Themer\Console\Commands;

use AlizHarb\Themer\ThemeManager;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class ThemeClearCommand extends Command
{
    protected $signature = 'theme:clear';
    protected $description = 'Remove the configuration cache file';

    public function handle(ThemeManager $manager): int
    {
        $cachePath = $manager->getCachePath();

        if (File::exists($cachePath)) {
            File::delete($cachePath);
            $this->components->info('Theme cache cleared!');
        } else {
            $this->components->info('No theme cache found.');
        }

        return self::SUCCESS;
    }
}
