<?php

declare(strict_types=1);

namespace AlizHarb\Themer\Events;

use Illuminate\Foundation\Events\Dispatchable;

final class ThemeCached
{
    use Dispatchable;

    public function __construct(public int $count) {}
}
