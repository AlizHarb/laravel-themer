<?php

declare(strict_types=1);

namespace AlizHarb\Themer\Console\Commands;

use AlizHarb\Themer\Exceptions\ThemerException;
use AlizHarb\Themer\ThemeManager;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Symfony\Component\Process\Process;
use Throwable;

use function Laravel\Prompts\search;

/**
 * Artisan command to activate a specific theme and update the .env file.
 */
final class ActivateThemeCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'theme:activate {theme? : The name of the theme to activate}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Activate a theme and update the environment file';

    /**
     * Execute the console command.
     */
    public function handle(ThemeManager $manager): int
    {
        /** @var string|null $themeName */
        $themeName = $this->argument('theme');

        if (! $themeName) {
            /** @var array<int, string> $themes */
            $themes = $manager->all()
                ->map(fn ($theme) => $theme->name)
                ->unique()
                ->values()
                ->toArray();

            if (empty($themes)) {
                $this->components->error('No themes discovered to activate.');

                return self::FAILURE;
            }

            /** @var string $themeName */
            $themeName = search(
                label: 'Which theme do you want to activate?',
                options: fn (string $value) => strlen($value) > 0
                    ? array_filter($themes, fn ($theme) => str_contains(strtolower($theme), strtolower($value)))
                    : $themes,
                placeholder: 'Search themes...'
            );
        }

        try {
            $manager->set((string) $themeName);

            $this->updateEnvironmentFile((string) $themeName);

            $this->components->info(sprintf('Theme [%s] activated successfully.', $themeName));

            if (config('themer.assets.publish_on_activate', true)) {
                $this->call('theme:publish', ['theme' => $themeName]);
            }

            $theme = $manager->find((string) $themeName);
            if ($theme && isset($theme->hooks['after_activate']) && is_array($theme->hooks['after_activate'])) {
                foreach ($theme->hooks['after_activate'] as $command) {
                    $this->components->info(sprintf('Running hook for [%s]: %s', $themeName, $command));
                    $process = Process::fromShellCommandline($command, base_path());
                    $process->run(function (string $type, string $buffer) {
                        $this->output->write($buffer);
                    });
                }
            }
        } catch (ThemerException $e) {
            $this->components->error($e->getMessage());

            return self::FAILURE;
        } catch (Throwable $e) {
            $this->components->error('Failed to activate theme: '.$e->getMessage());

            return self::FAILURE;
        }

        return self::SUCCESS;
    }

    /**
     * Update the .env file with the active theme.
     */
    protected function updateEnvironmentFile(string $themeName): void
    {
        $path = app()->environmentFilePath();

        if (! File::exists($path)) {
            return;
        }

        /** @var string $content */
        $content = File::get($path);

        if (str_contains($content, 'THEME=')) {
            $content = (string) preg_replace('/THEME=.*/', 'THEME="'.$themeName.'"', $content);
        } else {
            $content .= "\nTHEME=\"{$themeName}\"";
        }

        File::put($path, $content);
    }
}
