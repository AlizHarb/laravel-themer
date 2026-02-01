<?php

declare(strict_types=1);

namespace AlizHarb\Themer\Console\Commands;

use AlizHarb\Themer\ThemeManager;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class ThemeClearCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $name = 'theme:clear';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remove the configuration cache file';

    /**
     * Execute the console command.
     */
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

    /**
     * Get the console command options.
     *
     * @return array<int, array{0: string, 1: string|null, 2: int, 3: string, 4: mixed|null}>
     */
    protected function getOptions(): array
    {
        /** @var array<int, array{0: string, 1: string|null, 2: int, 3: string, 4: mixed|null}> $options */
        $options = parent::getOptions();

        return $options;
    }
}
