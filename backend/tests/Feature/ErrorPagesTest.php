<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ErrorPagesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['app.debug' => false]);
    }

    public function test_common_http_errors_render_custom_pages(): void
    {
        Route::get('/__test/errors/{status}', function (string $status) {
            abort((int) $status, 'Test exception message.');
        })->whereNumber('status');

        $cases = [
            400 => 'Bad request',
            401 => 'Sign in required',
            403 => 'Access restricted',
            404 => 'This page is not available',
            405 => 'This action is not available',
            419 => 'Page expired',
            429 => 'Too many attempts',
            500 => 'Something went wrong',
            503 => 'Temporarily unavailable',
        ];

        foreach ($cases as $status => $title) {
            $this->get('/__test/errors/' . $status)
                ->assertStatus($status)
                ->assertSee($title)
                ->assertSee('Error ' . $status)
                ->assertSee('button button-primary', false)
                ->assertDontSee('normalize.css');
        }
    }

    public function test_missing_route_uses_custom_404_page(): void
    {
        $this->get('/__test/missing-page-for-error-view')
            ->assertNotFound()
            ->assertSee('This page is not available')
            ->assertSee('Error 404');
    }

    public function test_unlisted_client_errors_use_custom_4xx_fallback(): void
    {
        Route::get('/__test/errors/fallback', function () {
            abort(418, 'Short and stout.');
        });

        $this->get('/__test/errors/fallback')
            ->assertStatus(418)
            ->assertSee('Request interrupted')
            ->assertSee('Error 418')
            ->assertDontSee('normalize.css');
    }

    public function test_unlisted_server_errors_use_custom_5xx_fallback(): void
    {
        Route::get('/__test/errors/server-fallback', function () {
            abort(502, 'Gateway issue.');
        });

        $this->get('/__test/errors/server-fallback')
            ->assertStatus(502)
            ->assertSee('Temporary service issue')
            ->assertSee('Error 502')
            ->assertDontSee('normalize.css');
    }
}
