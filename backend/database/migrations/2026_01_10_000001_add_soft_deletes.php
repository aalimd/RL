<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('requests', function (Blueprint $table) {
            if (!Schema::hasColumn('requests', 'deleted_at')) {
                $table->softDeletes();
            }
        });

        Schema::table('templates', function (Blueprint $table) {
            if (!Schema::hasColumn('templates', 'deleted_at')) {
                $table->softDeletes();
            }
        });
    }

    public function down(): void
    {
        Schema::table('requests', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('templates', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};
