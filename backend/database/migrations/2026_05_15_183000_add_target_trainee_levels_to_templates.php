<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('templates', function (Blueprint $table) {
            // target_trainee_levels will store a JSON array of levels this template is valid for.
            // If null or empty, it's considered valid for all levels (backward compatibility).
            if (!Schema::hasColumn('templates', 'target_trainee_levels')) {
                $table->json('target_trainee_levels')->after('is_active')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('templates', function (Blueprint $table) {
            $table->dropColumn('target_trainee_levels');
        });
    }
};
