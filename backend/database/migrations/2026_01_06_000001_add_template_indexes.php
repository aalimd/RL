<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        $driver = Schema::getConnection()->getDriverName();
        $indexes = $this->getIndexNames('templates', $driver);

        Schema::table('templates', function (Blueprint $table) use ($indexes) {
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

    private function getIndexNames(string $table, string $driver): array
    {
        if ($driver === 'sqlite') {
            return collect(DB::select("PRAGMA index_list('$table')"))->pluck('name')->all();
        }

        return collect(DB::select("SHOW INDEX FROM $table"))->pluck('Key_name')->all();
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
