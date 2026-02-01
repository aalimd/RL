<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('templates', function (Blueprint $table) {
            // Check for existing indexes to avoid duplicates
            $indexes = collect(\Illuminate\Support\Facades\DB::select("SHOW INDEX FROM templates"))->pluck('Key_name')->all();

            if (!in_array('templates_is_active_index', $indexes)) {
                $table->index('is_active');
            }
            if (!in_array('templates_language_index', $indexes)) {
                $table->index('language');
            }
            if (!in_array('templates_name_index', $indexes)) {
                $table->index('name');
            }
            if (!in_array('templates_created_at_index', $indexes)) {
                $table->index('created_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('templates', function (Blueprint $table) {
            $table->dropIndex(['is_active']);
            $table->dropIndex(['language']);
            $table->dropIndex(['name']);
            $table->dropIndex(['created_at']);
        });
    }
};
