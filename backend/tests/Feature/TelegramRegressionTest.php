<?php

namespace Tests\Feature;

use App\Models\Request as RequestRecord;
use App\Models\Settings;
use App\Services\TelegramService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Mockery;
use Tests\TestCase;

class TelegramRegressionTest extends TestCase
{
    use RefreshDatabase;

    public function test_telegram_callback_sends_only_one_student_update_message(): void
    {
        Mail::fake();
        Http::fake([
            'https://api.telegram.org/*' => Http::response(['ok' => true], 200),
        ]);

        Settings::updateOrCreate(['key' => 'telegram_webhook_secret'], ['value' => 'secret-token']);
        Settings::updateOrCreate(['key' => 'telegram_chat_id'], ['value' => '99999']);

        $request = RequestRecord::create([
            'tracking_id' => 'REC-2026-TG01',
            'student_name' => 'Telegram',
            'last_name' => 'Student',
            'student_email' => 'telegram@example.test',
            'verification_token' => 'ID-TG',
            'university' => 'Test University',
            'purpose' => 'Residency',
            'training_period' => '2026-04',
            'status' => 'Submitted',
            'telegram_chat_id' => '12345',
        ]);

        $telegramMock = Mockery::mock(TelegramService::class);
        $telegramMock->shouldReceive('sendMessage')
            ->once()
            ->andReturn(true);
        $telegramMock->shouldReceive('sendMessageToChat')
            ->once()
            ->withArgs(function ($chatId, $message) use ($request) {
                return (string) $chatId === (string) $request->telegram_chat_id
                    && str_contains($message, 'Approved');
            })
            ->andReturn(true);

        $this->app->instance(TelegramService::class, $telegramMock);

        $payload = [
            'callback_query' => [
                'id' => 'callback-1',
                'data' => 'approve_' . $request->id,
                'message' => [
                    'chat' => ['id' => '99999'],
                ],
            ],
        ];

        $this->withHeader('X-Telegram-Bot-Api-Secret-Token', 'secret-token')
            ->postJson('/api/telegram/webhook', $payload)
            ->assertOk();

        $request->refresh();

        $this->assertSame('Approved', $request->status);
    }
}
