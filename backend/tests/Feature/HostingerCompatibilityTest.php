<?php

namespace Tests\Feature;

use App\Models\Settings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class HostingerCompatibilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_media_route_serves_public_disk_files_without_storage_link(): void
    {
        $path = 'uploads/settings/hostinger-' . uniqid('', true) . '.txt';

        try {
            Storage::disk('public')->put($path, 'hostinger-media');

            $response = $this->get('/media/' . $path);

            $response->assertOk()
                ->assertHeader('X-Content-Type-Options', 'nosniff');

            $this->assertSame(
                Storage::disk('public')->path($path),
                $response->baseResponse->getFile()->getPathname()
            );
        } finally {
            Storage::disk('public')->delete($path);
        }
    }

    public function test_public_settings_rewrite_storage_urls_to_media_fallback_when_symlink_is_missing(): void
    {
        $path = 'uploads/settings/hostinger-' . uniqid('', true) . '.png';

        try {
            Storage::disk('public')->put($path, 'fake-image');

            Settings::create([
                'key' => 'logoUrl',
                'value' => '/storage/' . $path,
            ]);

            $expectedUrl = is_file(public_path('storage/' . $path))
                ? '/storage/' . $path
                : '/media/' . $path;

            $this->getJson('/api/settings/public')
                ->assertOk()
                ->assertJson([
                    'logoUrl' => $expectedUrl,
                ]);
        } finally {
            Storage::disk('public')->delete($path);
        }
    }
}
