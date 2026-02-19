<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('audit_logs')) {
            Schema::table('audit_logs', function (Blueprint $table) {
                if (!Schema::hasColumn('audit_logs', 'target_type')) {
                    $table->string('target_type')->nullable()->after('action');
                }
                if (!Schema::hasColumn('audit_logs', 'target_id')) {
                    $table->unsignedBigInteger('target_id')->nullable()->after('target_type');
                }
            });
        }

        if (Schema::hasTable('templates')) {
            Schema::table('templates', function (Blueprint $table) {
                if (!Schema::hasColumn('templates', 'draft_data')) {
                    $table->longText('draft_data')->nullable()->after('layout_settings');
                }
                if (!Schema::hasColumn('templates', 'last_draft_saved_at')) {
                    $table->timestamp('last_draft_saved_at')->nullable()->after('draft_data');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('audit_logs')) {
            Schema::table('audit_logs', function (Blueprint $table) {
                if (Schema::hasColumn('audit_logs', 'target_id')) {
                    $table->dropColumn('target_id');
                }
                if (Schema::hasColumn('audit_logs', 'target_type')) {
                    $table->dropColumn('target_type');
                }
            });
        }

        if (Schema::hasTable('templates')) {
            Schema::table('templates', function (Blueprint $table) {
                if (Schema::hasColumn('templates', 'last_draft_saved_at')) {
                    $table->dropColumn('last_draft_saved_at');
                }
                if (Schema::hasColumn('templates', 'draft_data')) {
                    $table->dropColumn('draft_data');
                }
            });
        }
    }
};
