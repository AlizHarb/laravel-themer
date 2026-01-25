<?php

declare(strict_types=1);

namespace AlizHarb\Themer\Http\Middleware;

use AlizHarb\Themer\ThemeManager;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetTheme
{
    /**
     * Create a new middleware instance.
     */
    public function __construct(
        protected ThemeManager $manager
    ) {
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $theme): Response
    {
        $this->manager->set($theme);

        return $next($request);
    }
}
