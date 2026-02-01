<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('email_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('subject');
            $table->text('body');
            $table->json('variables')->nullable();
            $table->timestamps();
        });

        // Insert Default Templates
        DB::table('email_templates')->insert([
            [
                'name' => 'two_factor_code',
                'subject' => 'Your Verification Code',
                'body' => '<p>Hello,</p><p>Your verification code is: <strong>{code}</strong></p><p>This code will expire in 10 minutes.</p>',
                'variables' => json_encode(['code']),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'request_status_update',
                'subject' => 'Update on your Request #{request_id}',
                'body' => '<p>Hello {name},</p><p>The status of your request has been updated to: <strong>{status}</strong></p>',
                'variables' => json_encode(['name', 'status', 'request_id']),
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('email_templates');
    }
};
