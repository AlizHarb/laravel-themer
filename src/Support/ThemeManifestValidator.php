<?php

declare(strict_types=1);

namespace AlizHarb\Themer\Support;

use Illuminate\Support\Str;

final class ThemeManifestValidator
{
    /**
     * Validate a decoded theme manifest.
     *
     * @param array<string, mixed>|null $manifest
     * @return array<int, string>
     */
    public function validate(?array $manifest, string $directory): array
    {
        if (! is_array($manifest)) {
            return ['theme.json must contain valid JSON object data.'];
        }

        $errors = [];
        $name = $manifest['name'] ?? null;
        $slug = $manifest['slug'] ?? null;

        if (! is_string($name) || trim($name) === '') {
            $errors[] = 'The [name] field is required and must be a non-empty string.';
        }

        if (! is_string($slug) || trim($slug) === '') {
            $errors[] = 'The [slug] field is required and must be a non-empty string.';
        } elseif (! preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $slug)) {
            $errors[] = 'The [slug] field must be lowercase kebab-case.';
        }

        if (is_string($name) && is_string($slug) && $slug !== Str::slug($name)) {
            $errors[] = 'The [slug] field does not match the theme name convention.';
        }

        if (array_key_exists('version', $manifest) && (! is_string($manifest['version']) || ! preg_match('/^\d+\.\d+\.\d+(?:[-+][0-9A-Za-z.-]+)?$/', $manifest['version']))) {
            $errors[] = 'The [version] field must be a semantic version string.';
        }

        foreach (['parent', 'asset_path', 'description', 'author'] as $key) {
            if (array_key_exists($key, $manifest) && ! is_null($manifest[$key]) && ! is_string($manifest[$key])) {
                $errors[] = "The [{$key}] field must be a string or null.";
            }
        }

        foreach (['removable', 'disableable'] as $key) {
            if (array_key_exists($key, $manifest) && ! is_bool($manifest[$key])) {
                $errors[] = "The [{$key}] field must be a boolean.";
            }
        }

        foreach (['screenshots', 'tags', 'conflicts', 'provides'] as $key) {
            if (array_key_exists($key, $manifest) && ! $this->isStringList($manifest[$key])) {
                $errors[] = "The [{$key}] field must be an array of strings.";
            }
        }

        if (array_key_exists('hooks', $manifest) && ! $this->isStringListMap($manifest['hooks'])) {
            $errors[] = 'The [hooks] field must be an object of string arrays.';
        }

        if (array_key_exists('tokens', $manifest) && ! $this->isTokenMap($manifest['tokens'])) {
            $errors[] = 'The [tokens] field must be an object of scalar design token values.';
        }

        if (array_key_exists('requires', $manifest) && ! $this->isRequiresShape($manifest['requires'])) {
            $errors[] = 'The [requires] field must be an object containing themes/modules arrays or a packages object.';
        }

        if (is_string($slug) && basename($directory) !== $slug) {
            $errors[] = 'The theme directory name should match the manifest slug.';
        }

        return $errors;
    }

    private function isStringList(mixed $value): bool
    {
        return is_array($value) && array_is_list($value) && collect($value)->every(fn (mixed $item): bool => is_string($item));
    }

    private function isStringListMap(mixed $value): bool
    {
        return is_array($value) && collect($value)->every(fn (mixed $items): bool => $this->isStringList($items));
    }

    private function isTokenMap(mixed $value): bool
    {
        return is_array($value) && collect($value)->every(fn (mixed $item): bool => is_string($item) || is_int($item) || is_float($item) || is_bool($item));
    }

    private function isRequiresShape(mixed $value): bool
    {
        if (! is_array($value)) {
            return false;
        }

        if (array_key_exists('themes', $value) && ! $this->isStringList($value['themes'])) {
            return false;
        }

        if (array_key_exists('modules', $value) && ! $this->isStringList($value['modules'])) {
            return false;
        }

        if (array_key_exists('packages', $value) && (! is_array($value['packages']) || ! collect($value['packages'])->every(fn (mixed $constraint, mixed $package): bool => is_string($package) && is_string($constraint)))) {
            return false;
        }

        return true;
    }
}
