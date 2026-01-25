<?php

declare(strict_types=1);

namespace AlizHarb\Themer\Facades;

use AlizHarb\Themer\ThemeManager;
use Illuminate\Support\Facades\Facade;

/**
 * @method static void register(\AlizHarb\Themer\Theme $theme)
 * @method static void scan(string $path)
 * @method static void set(string $themeName)
 * @method static \AlizHarb\Themer\Theme|null getActiveTheme()
 * @method static \Illuminate\Support\Collection<string, \AlizHarb\Themer\Theme> all()
 *
 * @see ThemeManager
 */
final class Theme extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return 'themer';
    }
}
