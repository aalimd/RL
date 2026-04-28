<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    private const TEMPLATE_NAMES = [
        'NGHA Emergency Medicine',
        'NGHA Official Framed Recommendation Letter',
    ];

    public function up(): void
    {
        if (!Schema::hasTable('templates')) {
            return;
        }

        $templates = DB::table('templates')
            ->whereIn('name', self::TEMPLATE_NAMES)
            ->when(Schema::hasColumn('templates', 'deleted_at'), static function ($query) {
                $query->whereNull('deleted_at');
            })
            ->get(['id', 'layout_settings']);

        foreach ($templates as $template) {
            DB::table('templates')
                ->where('id', $template->id)
                ->update([
                    'layout_settings' => $this->officialFramedLayoutSettings($template->layout_settings),
                    'updated_at' => now(),
                ]);
        }

        $this->clearTemplateCache();
    }

    public function down(): void
    {
        $this->clearTemplateCache();
    }

    private function officialFramedLayoutSettings(?string $existingSettings): string
    {
        $settings = json_decode((string) $existingSettings, true) ?: [];

        return json_encode(array_replace_recursive($settings, [
            'fontFamily' => "'Times New Roman', serif",
            'fontSize' => 10.4,
            'language' => 'en',
            'direction' => 'ltr',
            'margins' => [
                'top' => 18,
                'right' => 18,
                'bottom' => 14,
                'left' => 18,
            ],
            'border' => [
                'enabled' => false,
                'width' => 2,
                'style' => 'solid',
                'color' => '#2f8e55',
            ],
            'frame' => [
                'style' => 'ngha_green',
                'color' => '#2f8e55',
                'topInset' => 10,
                'sideInset' => 10,
                'bottomInset' => 8,
            ],
            'watermark' => [
                'enabled' => false,
                'text' => null,
            ],
            'qrCode' => [
                'enabled' => false,
            ],
            'footer' => [
                'enabled' => false,
            ],
            'pdfFit' => [
                'lineHeight' => 1.34,
                'paragraphGap' => 6,
                'headerGap' => 10,
                'bodyGap' => 8,
                'signatureTop' => 8,
                'signatureNameSize' => 10,
                'signatureTitleSize' => 7.6,
                'signatureDetailSize' => 7,
                'signatureImageHeight' => 42,
                'stampSize' => 72,
                'footerTop' => 4,
                'footerFontSize' => 6.8,
                'footerLineHeight' => 1.05,
                'borderPadding' => 9,
            ],
        ]), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    private function clearTemplateCache(): void
    {
        Cache::forget('active_template_v2');
        Cache::forget('all_templates_v2');
    }
};
