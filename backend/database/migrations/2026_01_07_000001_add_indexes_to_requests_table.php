<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('requests', function (Blueprint $table) {
            // Check for existing indexes to avoid duplicates
            $indexes = collect(\Illuminate\Support\Facades\DB::select("SHOW INDEX FROM requests"))->pluck('Key_name')->all();

            if (!in_array('requests_status_index', $indexes)) {
                $table->index('status');
            }
            if (!in_array('requests_student_email_index', $indexes)) {
                $table->index('student_email');
            }
            if (!in_array('requests_created_at_index', $indexes)) {
                $table->index('created_at');
            }
            // Composite index check (Laravel names it table_col1_col2_index by default)
            if (!in_array('requests_status_created_at_index', $indexes)) {
                $table->index(['status', 'created_at']);
            }
        });
    }

    public function down(): void
    {
        Schema::table('requests', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropIndex(['student_email']);
            $table->dropIndex(['created_at']);
            $table->dropIndex(['status', 'created_at']);
        });
    }
};
