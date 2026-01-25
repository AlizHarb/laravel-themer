<?php

declare(strict_types=1);

namespace AlizHarb\Themer;

/**
 * Data object representing a Theme.
 */
final readonly class Theme
{
    /**
     * Create a new Theme instance.
     *
     * @param  array<string, mixed>  $config
     */
    public function __construct(
        public string $name,
        public string $path,
        public string $assetPath = '',
        public ?string $parent = null,
        public array $config = []
    ) {
    }

    /**
     * Get the view namespace for the theme.
     */
    public function getViewNamespace(): string
    {
        return $this->name;
    }
}
