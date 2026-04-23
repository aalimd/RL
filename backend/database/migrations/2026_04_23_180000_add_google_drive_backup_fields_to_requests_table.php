<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('requests')) {
            return;
        }

        Schema::table('requests', function (Blueprint $table) {
            if (!Schema::hasColumn('requests', 'drive_backup_status')) {
                $table->string('drive_backup_status')->nullable()->after('document_path');
            }

            if (!Schema::hasColumn('requests', 'drive_backup_file_id')) {
                $table->string('drive_backup_file_id')->nullable()->after('drive_backup_status');
            }

            if (!Schema::hasColumn('requests', 'drive_backup_file_name')) {
                $table->string('drive_backup_file_name')->nullable()->after('drive_backup_file_id');
            }

            if (!Schema::hasColumn('requests', 'drive_backup_url')) {
                $table->text('drive_backup_url')->nullable()->after('drive_backup_file_name');
            }

            if (!Schema::hasColumn('requests', 'drive_backup_error')) {
                $table->text('drive_backup_error')->nullable()->after('drive_backup_url');
            }

            if (!Schema::hasColumn('requests', 'drive_backup_synced_at')) {
                $table->timestamp('drive_backup_synced_at')->nullable()->after('drive_backup_error');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('requests')) {
            return;
        }

        Schema::table('requests', function (Blueprint $table) {
            foreach ([
                'drive_backup_status',
                'drive_backup_file_id',
                'drive_backup_file_name',
                'drive_backup_url',
                'drive_backup_error',
                'drive_backup_synced_at',
            ] as $column) {
                if (Schema::hasColumn('requests', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
