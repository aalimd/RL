<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::transaction(function () {
            // Map Senior / Fellow template to its levels
            DB::table('templates')
                ->where('name', 'Senior / Fellow Recommendation Letter')
                ->update([
                    'target_trainee_levels' => json_encode(['Senior Registrar', 'Fellow'])
                ]);

            // Map NGHA Emergency Medicine to junior levels
            DB::table('templates')
                ->where('name', 'NGHA Emergency Medicine')
                ->update([
                    'target_trainee_levels' => json_encode(['Medical Student', 'Intern', 'Resident'])
                ]);
            
            // NGHA Official Framed stays NULL (available for all)
        });

        Cache::forget('active_template_v2');
        Cache::forget('all_templates_v2');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('templates')->update(['target_trainee_levels' => null]);
    }
};
