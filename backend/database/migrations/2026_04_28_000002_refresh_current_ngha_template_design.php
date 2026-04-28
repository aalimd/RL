<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    private const SOURCE_TEMPLATE_NAME = 'NGHA Official Framed Recommendation Letter';
    private const FALLBACK_CURRENT_TEMPLATE_NAME = 'NGHA Emergency Medicine';

    public function up(): void
    {
        if (!Schema::hasTable('templates')) {
            return;
        }

        $source = $this->activeTemplateByName(self::SOURCE_TEMPLATE_NAME);
        if (!$source) {
            return;
        }

        $target = $this->currentTemplate();
        if (!$target || (int) $target->id === (int) $source->id) {
            return;
        }

        DB::table('templates')
            ->where('id', $target->id)
            ->update([
                'content' => $source->body_content ?? $source->content,
                'header_content' => $source->header_content,
                'body_content' => $source->body_content,
                'footer_content' => $source->footer_content,
                'signature_name' => $source->signature_name,
                'signature_title' => $source->signature_title,
                'signature_image' => $source->signature_image,
                'stamp_image' => $source->stamp_image,
                'signature_institution' => $source->signature_institution,
                'signature_department' => $source->signature_department,
                'signature_email' => $source->signature_email,
                'signature_phone' => $source->signature_phone,
                'layout_settings' => $this->officialFramedLayoutSettings($source->layout_settings),
                'language' => $source->language,
                'is_active' => true,
                'draft_data' => null,
                'last_draft_saved_at' => null,
                'updated_at' => now(),
            ]);

        $this->clearTemplateCache();
    }

    public function down(): void
    {
        $this->clearTemplateCache();
    }

    private function currentTemplate(): ?object
    {
        $defaultTemplateId = null;

        if (Schema::hasTable('settings')) {
            $defaultTemplateId = DB::table('settings')
                ->where('key', 'defaultTemplateId')
                ->value('value');
        }

        if ($defaultTemplateId) {
            $template = DB::table('templates')
                ->where('id', (int) $defaultTemplateId)
                ->when(Schema::hasColumn('templates', 'deleted_at'), static function ($query) {
                    $query->whereNull('deleted_at');
                })
                ->first();

            if ($template) {
                return $template;
            }
        }

        return $this->activeTemplateByName(self::FALLBACK_CURRENT_TEMPLATE_NAME)
            ?: DB::table('templates')
                ->where('is_active', true)
                ->when(Schema::hasColumn('templates', 'deleted_at'), static function ($query) {
                    $query->whereNull('deleted_at');
                })
                ->orderBy('id')
                ->first();
    }

    private function activeTemplateByName(string $name): ?object
    {
        return DB::table('templates')
            ->where('name', $name)
            ->where('is_active', true)
            ->when(Schema::hasColumn('templates', 'deleted_at'), static function ($query) {
                $query->whereNull('deleted_at');
            })
            ->first();
    }

    private function officialFramedLayoutSettings(?string $existingSettings): string
    {
        $settings = json_decode((string) $existingSettings, true) ?: [];

        $settings['fontFamily'] = "'Times New Roman', serif";
        $settings['fontSize'] = 10.4;
        $settings['language'] = 'en';
        $settings['direction'] = 'ltr';
        $settings['margins'] = [
            'top' => 18,
            'right' => 18,
            'bottom' => 14,
            'left' => 18,
        ];
        $settings['border'] = [
            'enabled' => false,
            'width' => 2,
            'style' => 'solid',
            'color' => '#2f8e55',
        ];
        $settings['frame'] = [
            'style' => 'ngha_green',
            'color' => '#2f8e55',
            'topInset' => 10,
            'sideInset' => 10,
            'bottomInset' => 8,
        ];
        $settings['watermark'] = [
            'enabled' => false,
            'text' => null,
        ];
        $settings['qrCode'] = [
            'enabled' => false,
        ];
        $settings['footer'] = [
            'enabled' => false,
        ];
        $settings['pdfFit'] = [
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
        ];

        return json_encode($settings, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    private function clearTemplateCache(): void
    {
        Cache::forget('active_template_v2');
        Cache::forget('all_templates_v2');
    }
};
