<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('requests', function (Blueprint $table) {
            if (!Schema::hasColumn('requests', 'verification_token')) {
                $table->string('verification_token')->nullable()->after('student_email');
            }
            if (!Schema::hasColumn('requests', 'custom_content')) {
                $table->text('custom_content')->nullable()->after('purpose');
            }
            if (!Schema::hasColumn('requests', 'form_data')) {
                $table->longText('form_data')->nullable()->after('template_id'); // Changed to longText for encrypted data
            }
            if (!Schema::hasColumn('requests', 'admin_message')) {
                $table->text('admin_message')->nullable()->after('rejection_reason');
            }
        });
    }

    public function down(): void
    {
        Schema::table('requests', function (Blueprint $table) {
            $table->dropColumn(['verification_token', 'custom_content', 'form_data', 'admin_message']);
        });
    }
};
