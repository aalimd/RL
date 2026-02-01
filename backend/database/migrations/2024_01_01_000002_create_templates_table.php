<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('templates')) {
            Schema::create('templates', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->text('content')->nullable(); // Legacy
                $table->text('header_content')->nullable();
                $table->text('body_content')->nullable();
                $table->text('footer_content')->nullable();

                // Signature Fields
                $table->string('signature_name')->nullable();
                $table->string('signature_title')->nullable();
                $table->longText('signature_image')->nullable();
                $table->longText('stamp_image')->nullable();
                $table->string('signature_institution')->nullable();
                $table->string('signature_department')->nullable();
                $table->string('signature_email')->nullable();
                $table->string('signature_phone')->nullable();

                $table->json('layout_settings')->nullable();
                $table->enum('language', ['en', 'ar'])->default('en');
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('templates');
    }
};
