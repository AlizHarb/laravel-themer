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
        public array $config = [],
        public string $version = '1.0.0',
        public bool $hasViews = false,
        public bool $hasTranslations = false,
        public bool $hasProvider = false,
        public bool $hasLivewire = false
    ) {
    }

    /**
     * Get the view namespace for the theme.
     */
    public function getViewNamespace(): string
    {
        return strtolower($this->name);
    }

    /**
     * Convert the theme to an array for caching.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'path' => $this->path,
            'assetPath' => $this->assetPath,
            'parent' => $this->parent,
            'config' => $this->config,
            'version' => $this->version,
            'hasViews' => $this->hasViews,
            'hasTranslations' => $this->hasTranslations,
            'hasProvider' => $this->hasProvider,
            'hasLivewire' => $this->hasLivewire,
        ];
    }
}
