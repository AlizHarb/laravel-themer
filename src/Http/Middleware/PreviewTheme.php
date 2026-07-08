<?php

declare(strict_types=1);

namespace AlizHarb\Themer\Http\Middleware;

use AlizHarb\Themer\ThemeManager;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PreviewTheme
{
    /**
     * Create a new middleware instance.
     */
    public function __construct(
        protected ThemeManager $manager
    ) {}

    /**
     * Handle an incoming request.
     *
     * @param Closure(Request): (Response) $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $previewTheme = $request->query('preview_theme');

        if (is_string($previewTheme) && $previewTheme !== '') {
            $isLocal = app()->environment('local');
            $hasValidSignature = $request->hasValidSignature();
            $configEnabled = config('themer.preview.enabled', false);

            if ($isLocal || $hasValidSignature || $configEnabled) {
                try {
                    $this->manager->set($previewTheme);
                } catch (\Throwable) {
                    // Silently ignore invalid themes during preview
                }
            }
        }

        return $next($request);
    }
}
