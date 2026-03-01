<?php

declare(strict_types=1);

namespace AlizHarb\Themer\Console\Commands;

use AlizHarb\Themer\Exceptions\ThemerException;
use AlizHarb\Themer\Traits\HasThemeOption;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Symfony\Component\Process\Process;

/**
 * Artisan command to lint and format theme code.
 */
final class ThemeLintCommand extends Command
{
    use HasThemeOption;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'theme:lint {theme? : The name or slug of the theme to lint} {--php : Only lint PHP files using Laravel Pint} {--assets : Only lint frontend JS/CSS assets using NPM}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Lint and automatically format a specific theme\'s code';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        try {
            $themeName = $this->argument('theme') ?? $this->option('theme');

            $manager = app('themer');
            $theme = $manager->find((string) $themeName);
            if ($theme) {
                $theme = clone $theme;
            }

            if (! $theme) {
                // Try finding by active
                $theme = $manager->getActiveTheme();
                if (! $theme) {
                    throw new ThemerException("Theme [{$themeName}] not found and no active theme is set.");
                }
            }

            $this->components->info(sprintf('Linting Theme: %s [%s]', $theme->name, $theme->path));

            $ran = false;

            $runPhp = (bool) $this->option('php');
            $runAssets = (bool) $this->option('assets');

            if (! $runPhp && ! $runAssets) {
                $runPhp = true;
                $runAssets = true;
            }

            if ($runPhp) {
                $this->lintPHP($theme->path);
                $ran = true;
            }

            if ($runAssets) {
                $this->lintAssets($theme->path);
                $ran = true;
            }

            if ($ran) {
                $this->newLine();
                $this->components->info('Linting completed successfully.');
            }

            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->components->error($e->getMessage());

            return self::FAILURE;
        }
    }

    /**
     * Run Laravel Pint isolated to the theme directory.
     */
    protected function lintPHP(string $themePath): void
    {
        $this->components->task('Formatting PHP (Laravel Pint)', function () use ($themePath) {
            $pintBin = base_path('vendor/bin/pint');

            if (! File::exists($pintBin)) {
                $this->components->warn('Laravel Pint is not installed. Run `composer require laravel/pint --dev`');

                return false;
            }

            $process = Process::fromShellCommandline("{$pintBin} {$themePath}", base_path());
            $process->run();

            if (! $process->isSuccessful()) {
                $this->output->writeln($process->getErrorOutput());

                return false;
            }

            return true;
        });
    }

    /**
     * Run NPM lint/format scripts if defined in the theme's package.json.
     */
    protected function lintAssets(string $themePath): void
    {
        $packageJsonPath = $themePath.'/package.json';

        if (! File::exists($packageJsonPath)) {
            $this->components->task('Formatting Assets', function () {
                return false;
            });
            $this->components->warn('No package.json found in theme. Skipping asset linting.');

            return;
        }

        $packageJson = json_decode((string) File::get($packageJsonPath), true);
        $scripts = $packageJson['scripts'] ?? [];

        $scriptToRun = null;
        if (isset($scripts['format'])) {
            $scriptToRun = 'format';
        } elseif (isset($scripts['lint'])) {
            $scriptToRun = 'lint';
        }

        if (! $scriptToRun) {
            $this->components->task('Formatting Assets', function () {
                return false;
            });
            $this->components->warn('No "format" or "lint" script defined in theme\'s package.json. Skipping.');

            return;
        }

        $this->components->task("Formatting Assets (npm run {$scriptToRun})", function () use ($themePath, $scriptToRun) {
            $process = Process::fromShellCommandline("npm run {$scriptToRun}", $themePath);
            $process->run();

            if (! $process->isSuccessful()) {
                $this->output->writeln($process->getErrorOutput());

                return false;
            }

            return true;
        });
    }
}
