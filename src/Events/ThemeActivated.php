<?php

declare(strict_types=1);

namespace AlizHarb\Themer\Events;

use AlizHarb\Themer\Theme;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ThemeActivated
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(public Theme $theme)
    {
    }
}
