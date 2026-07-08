<?php

declare(strict_types=1);

namespace AlizHarb\Themer\Support;

use AlizHarb\Themer\Theme;
use AlizHarb\Themer\ThemeManager;
use Illuminate\Support\Facades\File;

final readonly class ThemeDiagnostics
{
    public function __construct(
        private ThemeManifestValidator $validator
    ) {}

    /**
     * @return array{
     *     status: string,
     *     summary: array<string, int|bool>,
     *     issues: array<int, array{level: string, theme?: string, message: string}>,
     *     themes: array<int, array<string, mixed>>
     * }
     */
    public function report(ThemeManager $manager): array
    {
        $issues = [];
        $themesPath = (string) config('themer.themes_path', base_path('themes'));

        if (! File::isDirectory($themesPath)) {
            $issues[] = ['level' => 'warning', 'message' => 'Themes directory does not exist.'];
        }

        $themes = $manager->all();
        $seenSlugs = [];
        $themeReports = [];

        foreach ($themes as $theme) {
            $manifestPath = $theme->path.'/theme.json';
            $manifest = File::exists($manifestPath)
                ? json_decode((string) File::get($manifestPath), true)
                : null;

            foreach ($this->validator->validate(is_array($manifest) ? $manifest : null, $theme->path) as $error) {
                $issues[] = ['level' => 'error', 'theme' => $theme->slug, 'message' => $error];
            }

            if (isset($seenSlugs[$theme->slug])) {
                $issues[] = ['level' => 'error', 'theme' => $theme->slug, 'message' => 'Duplicate theme slug detected.'];
            }
            $seenSlugs[$theme->slug] = true;

            if ($theme->parent && ! $manager->find($theme->parent)) {
                $issues[] = ['level' => 'error', 'theme' => $theme->slug, 'message' => "Missing parent theme [{$theme->parent}]."];
            }

            foreach ($theme->screenshots as $screenshot) {
                if (! File::exists($theme->path.'/'.$screenshot)) {
                    $issues[] = ['level' => 'error', 'theme' => $theme->slug, 'message' => "Missing screenshot [{$screenshot}]."];
                }
            }

            if (! $theme->parent && ! File::exists($theme->path.'/package.json')) {
                $issues[] = ['level' => 'warning', 'theme' => $theme->slug, 'message' => 'Missing package.json.'];
            }

            if (! $theme->parent && ! File::exists($theme->path.'/vite.config.js')) {
                $issues[] = ['level' => 'warning', 'theme' => $theme->slug, 'message' => 'Missing vite.config.js.'];
            }

            if (File::isDirectory($theme->path.'/node_modules')) {
                $issues[] = ['level' => 'warning', 'theme' => $theme->slug, 'message' => 'Theme contains node_modules; prefer root workspaces.'];
            }

            if (empty($theme->author)) {
                $issues[] = ['level' => 'warning', 'theme' => $theme->slug, 'message' => 'Theme has no author metadata.'];
            }

            $themeReports[] = [
                'name' => $theme->name,
                'slug' => $theme->slug,
                'version' => $theme->version,
                'parent' => $theme->parent,
                'path' => $theme->path,
                'resources' => [
                    'views' => $theme->hasViews,
                    'translations' => $theme->hasTranslations,
                    'provider' => $theme->hasProvider,
                    'livewire' => $theme->hasLivewire,
                    'package_json' => File::exists($theme->path.'/package.json'),
                    'vite_config' => File::exists($theme->path.'/vite.config.js'),
                ],
                'tokens' => $theme->tokens,
                'conflicts' => $theme->conflicts,
                'provides' => $theme->provides,
                'requires' => $theme->requires,
            ];
        }

        if ($manager->cacheIsStale()) {
            $issues[] = ['level' => 'warning', 'message' => 'Theme cache is stale. Run theme:refresh.'];
        }

        $hasErrors = collect($issues)->contains(fn (array $issue): bool => $issue['level'] === 'error');

        return [
            'status' => $hasErrors ? 'error' : ($issues === [] ? 'ok' : 'warning'),
            'summary' => [
                'themes' => count($themeReports),
                'issues' => count($issues),
                'errors' => collect($issues)->where('level', 'error')->count(),
                'warnings' => collect($issues)->where('level', 'warning')->count(),
                'cache_stale' => $manager->cacheIsStale(),
            ],
            'issues' => $issues,
            'themes' => $themeReports,
        ];
    }
}
