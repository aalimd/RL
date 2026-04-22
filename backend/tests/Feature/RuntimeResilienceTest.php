<?php

namespace Tests\Feature;

use App\Services\TelegramService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class RuntimeResilienceTest extends TestCase
{
    public function test_public_pages_render_when_database_becomes_unavailable(): void
    {
        $originalDatabase = config('database.connections.sqlite.database');

        try {
            config()->set('database.connections.sqlite.database', '/definitely/missing/runtime-resilience.sqlite');
            DB::disconnect('sqlite');
            DB::purge('sqlite');
            Cache::forget('maintenance_mode');

            $this->get('/')->assertOk();
            $this->get('/request')->assertOk();
            $this->get('/track')->assertOk();
        } finally {
            config()->set('database.connections.sqlite.database', $originalDatabase);
            DB::disconnect('sqlite');
            DB::purge('sqlite');
        }
    }

    public function test_telegram_service_get_bot_username_returns_null_when_settings_lookup_fails(): void
    {
        $originalDatabase = config('database.connections.sqlite.database');

        try {
            config()->set('database.connections.sqlite.database', '/definitely/missing/runtime-resilience.sqlite');
            DB::disconnect('sqlite');
            DB::purge('sqlite');

            $service = app(TelegramService::class);

            $this->assertNull($service->getBotUsername());
        } finally {
            config()->set('database.connections.sqlite.database', $originalDatabase);
            DB::disconnect('sqlite');
            DB::purge('sqlite');
        }
    }
}
