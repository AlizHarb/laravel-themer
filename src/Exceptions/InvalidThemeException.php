<?php

declare(strict_types=1);

namespace AlizHarb\Themer\Exceptions;

/**
 * Exception thrown when a theme configuration is invalid or missing required data.
 */
final class InvalidThemeException extends ThemerException
{
    /**
     * Create a new exception instance for an invalid theme configuration.
     */
    public static function invalidConfig(string $theme): self
    {
        return new self(sprintf('The configuration for theme [%s] is invalid.', $theme));
    }
}
