<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('requests', function (Blueprint $table) {
            $table->index('status');
            $table->index('student_email');
            $table->index('created_at');
            // Composite index for dashboard filtering + sorting
            $table->index(['status', 'created_at']);
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
