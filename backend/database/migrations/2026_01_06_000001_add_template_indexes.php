<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('templates', function (Blueprint $table) {
            $table->index('is_active');
            $table->index('language');
            $table->index('name');
            $table->index('created_at');
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
