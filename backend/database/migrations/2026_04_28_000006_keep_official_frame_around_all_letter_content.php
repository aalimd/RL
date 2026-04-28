<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        DB::table('templates')
            ->whereNotNull('layout_settings')
            ->orderBy('id')
            ->get(['id', 'layout_settings'])
            ->each(function ($template): void {
                $settings = json_decode((string) $template->layout_settings, true);

                if (!is_array($settings) || (($settings['frame']['style'] ?? null) !== 'ngha_green')) {
                    return;
                }

                $bottomInset = (float) ($settings['frame']['bottomInset'] ?? 8);
                if ($bottomInset > 18 || $bottomInset < 6) {
                    $settings['frame']['bottomInset'] = 8;
                }

                DB::table('templates')
                    ->where('id', $template->id)
                    ->update([
                        'layout_settings' => json_encode($settings, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                        'updated_at' => now(),
                    ]);
            });

        $this->clearTemplateCache();
    }

    public function down(): void
    {
        $this->clearTemplateCache();
    }

    private function clearTemplateCache(): void
    {
        Cache::forget('active_template_v2');
        Cache::forget('all_templates_v2');
    }
};
