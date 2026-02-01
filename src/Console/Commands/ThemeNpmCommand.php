<?php

declare(strict_types=1);

namespace AlizHarb\Themer\Console\Commands;

use AlizHarb\Themer\ThemeManager;
use Illuminate\Console\Command;
use Symfony\Component\Process\Process;

/**
 * Execute NPM commands for a specific theme.
 */
final class ThemeNpmCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'theme:npm {args* : The NPM command to execute} {--theme= : The name or slug of the theme}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Execute NPM commands for a specific theme.';

    /**
     * Execute the console command.
     */
    public function handle(ThemeManager $manager): int
    {
        /** @var string|null $themeName */
        $themeName = $this->option('theme');

        if (! is_string($themeName)) {
            $this->components->error('Theme name is required.');

            return self::FAILURE;
        }

        $theme = $manager->find($themeName);

        if (! $theme) {
            $this->components->error("Theme [{$themeName}] not found!");

            return self::FAILURE;
        }

        /** @var array<int, string> $commandParts */
        $commandParts = (array) $this->argument('args');
        $command = implode(' ', $commandParts);

        if ($command === 'install' && ! file_exists($theme->path.'/package.json')) {
            $this->components->warn("No package.json found for theme '{$theme->name}'. Skipping.");

            return self::SUCCESS;
        }

        $this->info("Executing 'npm {$command}' for theme: {$theme->name}");

        $process = new Process(['npm', ...$commandParts], $theme->path);
        $process->setTimeout(null);
        $process->run(function ($type, $buffer) {
            $this->output->write($buffer);
        });

        if (! $process->isSuccessful()) {
            $this->components->error("NPM command failed for theme: {$theme->name}");

            return self::FAILURE;
        }

        $this->components->info('NPM command completed successfully.');

        return self::SUCCESS;
    }
}
