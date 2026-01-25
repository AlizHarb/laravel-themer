<?php

declare(strict_types=1);

namespace AlizHarb\Themer\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ThemeActivating
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(public string $themeName)
    {
    }
}
