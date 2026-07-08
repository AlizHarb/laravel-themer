<?php

declare(strict_types=1);

namespace AlizHarb\Themer\Console\Commands;

use AlizHarb\Themer\Events\ThemeRefreshed;
use AlizHarb\Themer\Events\ThemeRefreshing;
use AlizHarb\Themer\ThemeManager;
use Illuminate\Console\Command;

final class ThemeRefreshCommand extends Command
{
    protected $signature = 'theme:refresh';

    protected $description = 'Clear and rebuild the theme discovery cache';

    public function handle(ThemeManager $manager): int
    {
        ThemeRefreshing::dispatch();

        $this->call('theme:clear');
        $status = $this->call('theme:cache');

        if ($status === self::SUCCESS) {
            ThemeRefreshed::dispatch($manager->all()->count());
        }

        return $status;
    }
}
