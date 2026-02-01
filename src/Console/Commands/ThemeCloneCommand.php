<?php

declare(strict_types=1);

namespace AlizHarb\Themer\Console\Commands;

use AlizHarb\Themer\ThemeManager;
use AlizHarb\Themer\Traits\HasThemeOption;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

/**
 * Console command to clone an existing theme.
 */
final class ThemeCloneCommand extends Command
{
    use HasThemeOption;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $name = 'theme:clone';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clone an existing theme into a new one';

    /**
     * Execute the console command.
     */
    public function handle(ThemeManager $manager): int
    {
        $sourceThemeName = (string) $this->getTheme();
        $sourceTheme = $manager->find($sourceThemeName);

        if (! $sourceTheme) {
            $this->components->error("Source theme [{$sourceThemeName}] not found!");

            return self::FAILURE;
        }

        $newName = (string) $this->argument('name');
        $newSlug = Str::slug($newName);
        $newPath = dirname($sourceTheme->path).DIRECTORY_SEPARATOR.$newSlug;

        if (File::exists($newPath)) {
            $this->components->error("Target path [{$newPath}] already exists.");

            return self::FAILURE;
        }

        $this->components->info("Cloning [{$sourceTheme->name}] into [{$newName}]...");

        if (File::copyDirectory($sourceTheme->path, $newPath)) {
            $this->line('  - Copied directory structure');

            // Update theme.json
            $themeJsonPath = $newPath.DIRECTORY_SEPARATOR.'theme.json';
            if (File::exists($themeJsonPath)) {
                /** @var array<string, mixed> $config */
                $config = json_decode((string) File::get($themeJsonPath), true);
                $config['name'] = $newName;
                $config['slug'] = $newSlug;
                $config['asset_path'] = 'themes/'.$newSlug;

                File::put($themeJsonPath, (string) json_encode($config, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
                $this->line('  - Updated theme.json');
            }

            // Update package.json if exists
            $packageJsonPath = $newPath.DIRECTORY_SEPARATOR.'package.json';
            if (File::exists($packageJsonPath)) {
                /** @var array<string, mixed> $package */
                $package = json_decode((string) File::get($packageJsonPath), true);
                $package['name'] = '@themes/'.$newSlug;

                File::put($packageJsonPath, (string) json_encode($package, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
                $this->line('  - Updated package.json');
            }

            $this->components->info("Theme [{$newName}] created successfully at [{$newPath}].");
            $this->components->info("Don't forget to run [npm install] to register the new workspace.");

            return self::SUCCESS;
        }

        $this->components->error('Failed to clone theme directory.');

        return self::FAILURE;
    }

    /**
     * Get the console command options.
     *
     * @return array<int, array{0: string, 1: string|null, 2: int, 3: string, 4: mixed|null}>
     */
    protected function getOptions(): array
    {
        /** @var array<int, array{0: string, 1: string|null, 2: int, 3: string, 4: mixed|null}> $options */
        $options = array_merge([
            ['theme', null, \Symfony\Component\Console\Input\InputOption::VALUE_REQUIRED, 'The name or slug of the theme to clone', null],
        ], parent::getOptions());

        return $options;
    }

    /**
     * Get the console command arguments.
     *
     * @return array<int, array{0: string, 1: int, 2: string, 3: mixed|null}>
     */
    protected function getArguments(): array
    {
        return [
            ['name', \Symfony\Component\Console\Input\InputArgument::REQUIRED, 'The name of the new theme', null],
        ];
    }
}
