<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('settings')) {
            return;
        }

        DB::table('settings')->updateOrInsert(
            ['key' => 'maintenanceMode'],
            [
                'value' => 'false',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        Cache::forget('maintenance_mode');
    }

    public function down(): void
    {
        Cache::forget('maintenance_mode');
    }
};
