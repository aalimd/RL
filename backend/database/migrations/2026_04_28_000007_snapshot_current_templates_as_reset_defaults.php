<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('templates')) {
            return;
        }

        Schema::table('templates', function (Blueprint $table) {
            if (!Schema::hasColumn('templates', 'reset_data')) {
                $table->longText('reset_data')->nullable()->after('last_draft_saved_at');
            }
            if (!Schema::hasColumn('templates', 'reset_saved_at')) {
                $table->timestamp('reset_saved_at')->nullable()->after('reset_data');
            }
        });

        DB::table('templates')
            ->whereNull('deleted_at')
            ->orderBy('id')
            ->get()
            ->each(function ($template): void {
                DB::table('templates')
                    ->where('id', $template->id)
                    ->update([
                        'reset_data' => json_encode($this->snapshot($template), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                        'reset_saved_at' => now(),
                    ]);
            });

        $this->clearTemplateCache();
    }

    public function down(): void
    {
        if (!Schema::hasTable('templates')) {
            return;
        }

        Schema::table('templates', function (Blueprint $table) {
            if (Schema::hasColumn('templates', 'reset_saved_at')) {
                $table->dropColumn('reset_saved_at');
            }
            if (Schema::hasColumn('templates', 'reset_data')) {
                $table->dropColumn('reset_data');
            }
        });

        $this->clearTemplateCache();
    }

    private function snapshot(object $template): array
    {
        $layoutSettings = [];
        if (!empty($template->layout_settings)) {
            $decoded = json_decode((string) $template->layout_settings, true);
            $layoutSettings = is_array($decoded) ? $decoded : [];
        }

        return [
            'name' => $template->name,
            'header_content' => $template->header_content,
            'body_content' => $template->body_content ?? $template->content,
            'footer_content' => $template->footer_content,
            'content' => $template->body_content ?? $template->content,
            'signature_name' => $template->signature_name,
            'signature_title' => $template->signature_title,
            'signature_department' => $template->signature_department,
            'signature_institution' => $template->signature_institution,
            'signature_email' => $template->signature_email,
            'signature_phone' => $template->signature_phone,
            'signature_image' => $template->signature_image,
            'stamp_image' => $template->stamp_image,
            'language' => $template->language,
            'is_active' => (bool) $template->is_active,
            'layout_settings' => $layoutSettings,
        ];
    }

    private function clearTemplateCache(): void
    {
        Cache::forget('active_template_v2');
        Cache::forget('all_templates_v2');
    }
};
