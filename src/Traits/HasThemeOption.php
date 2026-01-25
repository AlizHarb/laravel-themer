<?php

declare(strict_types=1);

namespace AlizHarb\Themer\Traits;

use Symfony\Component\Console\Input\InputOption;

/**
 * Trait to add the --theme option to Artisan commands.
 */
trait HasThemeOption
{
    /**
     * Determine if the command is running for a specific theme.
     */
    protected function isTheme(): bool
    {
        return $this->option('theme') !== null;
    }

    /**
     * Get the theme name from the option.
     */
    protected function getTheme(): ?string
    {
        /** @var mixed $theme */
        $theme = $this->option('theme');

        return is_string($theme) ? $theme : null;
    }

    /**
     * Get the console command options.
     *
     * @return array<int, array<int, mixed>>
     */
    protected function getOptions(): array
    {
        /** @var array<int, array<int, mixed>> $options */
        $options = parent::getOptions();

        // Check if theme option is already defined (e.g. in signature or parent)
        foreach ($options as $option) {
            if ($option[0] === 'theme') {
                return $options;
            }
        }

        return array_merge($options, [
            ['theme', null, InputOption::VALUE_OPTIONAL, 'The name of the theme.'],
        ]);
    }
}
