<?php

declare(strict_types=1);

namespace AlizHarb\Themer\Facades;

use AlizHarb\Themer\Theme as ThemeInstance;
use AlizHarb\Themer\ThemeManager;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Facade;

/**
 * @method static void register(ThemeInstance $theme)
 * @method static void scan(string $path)
 * @method static void set(string $themeName)
 * @method static ThemeInstance|null getActiveTheme()
 * @method static Collection<string, ThemeInstance> all()
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
