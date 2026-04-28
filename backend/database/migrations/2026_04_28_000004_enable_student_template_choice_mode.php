<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('settings') || !Schema::hasTable('templates')) {
            return;
        }

        $this->setSetting('templateSelectionMode', 'student_choice');
        $this->setSetting('defaultTemplateId', '');
        // Empty means "all active templates"; admins can later save an explicit allow-list.
        $this->setSetting('studentTemplateIds', '[]');
    }

    public function down(): void
    {
        if (!Schema::hasTable('settings')) {
            return;
        }

        $this->setSetting('templateSelectionMode', 'admin_fixed');
        $this->setSetting('defaultTemplateId', '1');
    }

    private function setSetting(string $key, string $value): void
    {
        DB::table('settings')->updateOrInsert(
            ['key' => $key],
            [
                'value' => $value,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        );
    }
};
