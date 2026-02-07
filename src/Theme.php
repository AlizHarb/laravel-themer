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
     * @param array<string, mixed> $config
     * @param array<int, array{name: string, email?: string, role?: string}> $authors
     */
    public function __construct(
        public string $name,
        public string $slug,
        public string $path,
        public string $assetPath = '',
        public ?string $parent = null,
        public array $config = [],
        public string $version = '1.0.0',
        public ?string $author = null,
        public array $authors = [],
        public bool $hasViews = false,
        public bool $hasTranslations = false,
        public bool $hasProvider = false,
        public bool $hasLivewire = false,
        public bool $removable = true,
        public bool $disableable = true,
        public array $screenshots = [],
        public array $tags = []
    ) {}

    /**
     * Get the view namespace for the theme.
     */
    public function getViewNamespace(): string
    {
        return $this->slug;
    }

    /**
     * Check if the theme has a parent.
     */
    public function isChild(): bool
    {
        return ! empty($this->parent);
    }

    /**
     * Check if the theme is a base theme (no parent).
     */
    public function isParent(): bool
    {
        return empty($this->parent);
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
            'slug' => $this->slug,
            'path' => $this->path,
            'assetPath' => $this->assetPath,
            'parent' => $this->parent,
            'config' => $this->config,
            'version' => $this->version,
            'author' => $this->author,
            'authors' => $this->authors,
            'hasViews' => $this->hasViews,
            'hasTranslations' => $this->hasTranslations,
            'hasProvider' => $this->hasProvider,
            'hasLivewire' => $this->hasLivewire,
            'removable' => $this->removable,
            'disableable' => $this->disableable,
            'screenshots' => $this->screenshots,
            'tags' => $this->tags,
        ];
    }
}
