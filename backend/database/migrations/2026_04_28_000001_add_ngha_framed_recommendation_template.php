<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    private const TEMPLATE_NAME = 'NGHA Official Framed Recommendation Letter';

    public function up(): void
    {
        if (!Schema::hasTable('templates')) {
            return;
        }

        $existing = DB::table('templates')->where('name', self::TEMPLATE_NAME);
        if (Schema::hasColumn('templates', 'deleted_at')) {
            $existing->whereNull('deleted_at');
        }

        if ($existing->exists()) {
            return;
        }

        $headerContent = <<<'HTML'
<table style="width: 100%; border-collapse: collapse; border: none; font-family: 'Times New Roman', serif;">
    <tbody>
        <tr>
            <td style="width: 36%; vertical-align: top; text-align: left; padding: 0; line-height: 1.15; font-size: 10px; color: #000;">
                <div style="font-weight: bold;">Kingdom of Saudi Arabia</div>
                <div>National Guard</div>
                <div>Health Affairs</div>
                <div>King Abdulaziz Medical City - Jeddah</div>
                <div>King Khalid National Guard Hospital</div>
                <div style="height: 5px;"></div>
                <div style="color: #b11f24; font-weight: bold;">Department of Emergency Medicine</div>
                <div style="color: #b11f24; font-weight: bold;">Tel: 012-2266666</div>
                <div style="color: #b11f24; font-weight: bold;">Ext: 62790-62791</div>
                <div style="color: #b11f24; font-weight: bold;">Email: emerg@mngha.med.sa</div>
            </td>
            <td style="width: 28%; vertical-align: top; text-align: center; padding: 0;">
                <img src="https://i.ibb.co/JW3Q0t7Y/mnghalogo.png" alt="NGHA Logo" style="width: 92px; height: auto;">
            </td>
            <td style="width: 36%; vertical-align: top; text-align: right; direction: rtl; padding: 0; line-height: 1.18; font-size: 11px; color: #000; font-family: 'DejaVu Sans', sans-serif;">
                <div style="font-weight: bold;">المملكة العربية السعودية</div>
                <div>وزارة الحرس الوطني</div>
                <div>الشؤون الصحية</div>
                <div>مدينة الملك عبد العزيز الطبية بجدة</div>
                <div>مستشفى الملك خالد الحرس الوطني</div>
            </td>
        </tr>
    </tbody>
</table>
HTML;

        $bodyContent = <<<'HTML'
<div style="font-family: 'Times New Roman', serif; font-size: 10.4pt; color: #000; line-height: 1.44;">
    <h2 style="text-align: center; font-weight: bold; font-size: 12pt; margin: 16px 0 14px 0; text-decoration: underline;">Recommendation Letter</h2>
    <h3 style="text-align: center; font-weight: bold; font-size: 11pt; margin: 0 0 24px 0;">Dr. {{fullName}}</h3>

    <p style="margin: 0 0 13px 0; text-align: left;">To Whom It May Concern,</p>

    <p style="margin: 0 0 13px 0; text-align: justify;">
        I am writing to express my strong support for Dr. {{fullName}}'s application to your residency program. I had the opportunity to supervise and evaluate Dr. {{lastName}} during {{his}} Emergency Department intern rotation in {{trainingPeriod}}. Over the course of the month, {{he}} proved to be a dedicated, well-rounded, and highly capable young physician.
    </p>

    <p style="margin: 0 0 13px 0; text-align: justify;">
        In the clinical setting, Dr. {{lastName}} demonstrated a solid foundation of medical knowledge and a systematic approach to patient care. {{He}} was consistently thorough in {{his}} history-taking and physical examinations, and {{his}} case presentations were organized and relevant. I was particularly impressed by {{his}} safe approach to undifferentiated patients; {{he}} recognized {{his}} own limits, knew exactly when to ask for attending input, and formulated sound differential diagnoses.
    </p>

    <p style="margin: 0 0 13px 0; text-align: justify;">
        Beyond {{his}} clinical skills, Dr. {{lastName}} is a consummate professional. The ER environment can be incredibly stressful, but {{he}} always maintained a calm and respectful demeanor. {{He}} communicates effectively not only with patients and their families, ensuring they understand their care plans, but also with the nursing staff and our interdisciplinary teams.
    </p>

    <p style="margin: 0 0 24px 0; text-align: justify;">
        Dr. {{lastName}} is a reliable, hardworking, and compassionate physician who will undoubtedly thrive in residency training. I recommend {{him}} highly and without reservation.
    </p>

    <p style="margin: 0; text-align: left;">Sincerely,</p>
</div>
HTML;

        $footerContent = <<<'HTML'
<table style="width: 100%; border-collapse: collapse; font-family: 'Times New Roman', serif; font-size: 8px; line-height: 1.18; color: #000;">
    <tbody>
        <tr>
            <td style="width: 38%; vertical-align: top; text-align: left; font-weight: bold;">
                P.O. BOX 9515<br>
                JEDDAH 21423<br>
                KINGDOM OF SAUDI ARABIA
            </td>
            <td style="width: 24%; vertical-align: top; text-align: center; font-weight: bold;">
                FAX: 624 7444
            </td>
            <td style="width: 38%; vertical-align: top; text-align: right; direction: rtl; font-family: 'DejaVu Sans', sans-serif; font-weight: bold;">
                ص.ب 9515<br>
                جدة 21423<br>
                المملكة العربية السعودية
            </td>
        </tr>
    </tbody>
</table>
HTML;

        $layoutSettings = [
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
        ];

        $now = now();

        DB::table('templates')->insert([
            'name' => self::TEMPLATE_NAME,
            'content' => $bodyContent,
            'header_content' => $headerContent,
            'body_content' => $bodyContent,
            'footer_content' => $footerContent,
            'signature_name' => 'Dr.Abdulrhman Al Zaharani',
            'signature_title' => 'ASSOCIATE CONSULTANT PEDIATRIC EMERGENCY MEDICINE',
            'signature_image' => 'https://i.ibb.co/KpLhP9GV/sign-preview-rev-1.png',
            'stamp_image' => null,
            'signature_institution' => 'Ministry of National Guard Health Affairs',
            'signature_department' => 'King Abdulaziz Medical City-Jeddah',
            'signature_email' => 'ZahraniAB13@MNGHA.MED.SA',
            'signature_phone' => null,
            'layout_settings' => json_encode($layoutSettings, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'language' => 'en',
            'is_active' => true,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $this->clearTemplateCache();
    }

    public function down(): void
    {
        if (!Schema::hasTable('templates')) {
            return;
        }

        DB::table('templates')->where('name', self::TEMPLATE_NAME)->delete();
        $this->clearTemplateCache();
    }

    private function clearTemplateCache(): void
    {
        Cache::forget('active_template_v2');
        Cache::forget('all_templates_v2');
    }
};
