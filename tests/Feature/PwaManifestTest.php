<?php

namespace Tests\Feature;

use Tests\TestCase;

class PwaManifestTest extends TestCase
{
    public function test_manifest_is_public_and_valid(): void
    {
        $response = $this->get(route('pwa.manifest'));

        $response->assertOk();
        $response->assertHeader('content-type', 'application/manifest+json');

        $response->assertJsonStructure([
            'name',
            'short_name',
            'start_url',
            'scope',
            'display',
            'theme_color',
            'background_color',
            'icons',
        ]);

        $response->assertJson([
            'start_url' => url('/'),
            'display' => config('pwa.display'),
            'theme_color' => config('pwa.theme_color'),
            'short_name' => config('pwa.short_name'),
        ]);

        $icons = $response->json('icons');
        $this->assertNotEmpty($icons);
        $this->assertArrayHasKey('src', $icons[0]);
    }
}
