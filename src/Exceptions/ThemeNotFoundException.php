<?php

declare(strict_types=1);

namespace AlizHarb\Themer\Exceptions;

/**
 * Exception thrown when a requested theme cannot be found.
 */
final class ThemeNotFoundException extends ThemerException
{
    /**
     * Create a new exception instance for a missing theme.
     */
    public static function make(string $theme): self
    {
        return new self(sprintf('Theme [%s] not found.', $theme));
    }
}
