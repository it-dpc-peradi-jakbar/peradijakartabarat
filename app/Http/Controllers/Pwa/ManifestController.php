<?php

namespace App\Http\Controllers\Pwa;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class ManifestController extends Controller
{
    public function __invoke(): JsonResponse
    {
        $icons = collect(config('pwa.icons', []))
            ->map(fn (array $icon) => [
                'src' => asset($icon['path']),
                'sizes' => $icon['sizes'],
                'type' => $icon['type'],
                'purpose' => $icon['purpose'],
            ])
            ->values()
            ->all();

        return response()->json([
            'name' => config('app.name'),
            'short_name' => config('pwa.short_name'),
            'description' => 'Portal Kantong Magang Advokat DPC PERADI Jakarta Barat.',
            'start_url' => url('/'),
            'scope' => $this->scope(),
            'display' => config('pwa.display'),
            'lang' => config('pwa.lang'),
            'theme_color' => config('pwa.theme_color'),
            'background_color' => config('pwa.background_color'),
            'icons' => $icons,
        ], 200, [
            'Content-Type' => 'application/manifest+json',
        ]);
    }

    protected function scope(): string
    {
        $rootUrl = config('app.url');
        if (! is_string($rootUrl) || $rootUrl === '') {
            return '/';
        }

        $path = parse_url($rootUrl, PHP_URL_PATH);
        if (! is_string($path) || $path === '' || $path === '/') {
            return '/';
        }

        return rtrim($path, '/').'/';
    }
}
