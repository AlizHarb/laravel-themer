<?php

declare(strict_types=1);

namespace AlizHarb\Themer\Console\Commands;

use AlizHarb\Themer\ThemeManager;
use AlizHarb\Themer\Traits\HasThemeOption;
use Illuminate\Console\Command;
use Symfony\Component\Process\Process;

/**
 * Console command to run npm dev for a specific theme.
 */
final class ThemeDevCommand extends Command
{
    use HasThemeOption;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $name = 'theme:dev';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run npm dev for a specific theme workspace';

    /**
     * Execute the console command.
     */
    public function handle(ThemeManager $manager): int
    {
        $themeName = (string) $this->getTheme();
        $theme = $manager->find($themeName);

        if (! $theme) {
            $this->components->error("Theme [{$themeName}] not found!");

            return self::FAILURE;
        }

        $this->components->info("Running: npm run dev -w @themes/{$theme->slug}");

        $process = new Process(['npm', 'run', 'dev', '-w', '@themes/'.$theme->slug], base_path(), null, null, null);

        if (Process::isTtySupported()) {
            $process->setTty(true);
        }

        $process->setTimeout(null);

        $process->run(function ($type, $buffer) {
            $this->output->write($buffer);
        });

        if (! $process->isSuccessful()) {
            return self::FAILURE;
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
        return array_merge(parent::getOptions(), $this->getThemeOptions());
    }
}
