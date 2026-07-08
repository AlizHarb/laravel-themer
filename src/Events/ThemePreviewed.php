<?php

declare(strict_types=1);

namespace AlizHarb\Themer\Events;

use Illuminate\Foundation\Events\Dispatchable;

final class ThemePreviewed
{
    use Dispatchable;

    public function __construct(public string $theme, public string $url) {}
}
