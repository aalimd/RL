<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Cache;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('templates')) {
            return;
        }

        DB::transaction(function () {
            // 1. Deploy the "Senior / Fellow Recommendation Letter" Template
            $templateName = 'Senior / Fellow Recommendation Letter';
            
            // Content with correct placeholders for Senior/Fellow levels
            $bodyContent = '<div style="font-family:\'Times New Roman\', serif;font-size:11pt;color:#000;line-height:1.5;">
<h2 style="text-align:center;font-weight:bold;margin:25px 0 25px 0;">Letter of Recommendation</h2>
<h3 style="text-align:center;font-weight:bold;margin:0 0 20px 0;">Dr. {{fullName}}</h3>
<p style="margin-bottom:15px;text-align:justify;"><strong>To Whom It May Concern,</strong></p>
<p style="margin-bottom:15px;text-align:justify;">It is with great pleasure and high regard that I write this letter of recommendation for <strong>Dr. {{lastName}}</strong>. I have had the distinct privilege of working closely with {{him}} during {{his}} tenure as a <strong>{{traineeLevel}}</strong> in the <strong>{{department}}</strong> at <strong>{{workLocation}}</strong> during <strong>{{trainingPeriod}}</strong>.</p>
<p style="margin-bottom:15px;text-align:justify;">Operating at the level of a {{traineeLevel}} requires exceptional clinical judgment, advanced procedural skills, and the ability to lead and educate junior colleagues. <strong>Dr. {{lastName}}</strong> consistently exceeded these expectations. {{He}} demonstrated a profound depth of medical knowledge, managing complex cases with confidence and evidence-based practice. {{His}} clinical acumen and decisive approach have made {{him}} an invaluable asset to our department.</p>
<p style="margin-bottom:15px;text-align:justify;">Beyond {{his}} clinical excellence, <strong>Dr. {{lastName}}</strong> exhibits outstanding leadership and communication skills. {{He}} is a natural educator, frequently taking the time to mentor junior staff, fostering a collaborative and supportive team environment. Furthermore, {{his}} interactions with patients and their families are consistently marked by empathy, respect, and clear communication.</p>
<p style="margin-bottom:20px;text-align:justify;">I have the utmost confidence in <strong>Dr. {{lastName}}\'s</strong> abilities and professional trajectory. {{He}} is a highly competent and compassionate physician who will undoubtedly excel in their chosen path. I strongly support {{his}} application for {{purpose}} without reservation.</p>
<p style="margin:0;text-align:left;">Sincerely,</p>
</div>';

            $headerContent = '<table style="width:100%;border-collapse:collapse;border:none;font-family:\'Times New Roman\', serif;">
<tbody>
<tr>
<td style="width:36%;vertical-align:top;text-align:left;padding:0;line-height:1.15;font-size:10px;color:#000;">
<div style="font-weight:bold;">Kingdom of Saudi Arabia</div>
<div>National Guard</div>
<div>Health Affairs</div>
<div>King Abdulaziz Medical City - Jeddah</div>
<div>King Khalid National Guard Hospital</div>
<div style="height:5px;"></div>
<div style="color:#b11f24;font-weight:bold;">Department of Emergency Medicine</div>
<div style="color:#b11f24;font-weight:bold;">Tel: 012-2266666</div>
<div style="color:#b11f24;font-weight:bold;">Ext: 62790-62791</div>
<div style="color:#b11f24;font-weight:bold;">Email: emerg@mngha.med.sa</div>
</td>
<td style="width:28%;vertical-align:top;text-align:center;padding:0;"><img src="https://i.ibb.co/JW3Q0t7Y/mnghalogo.png" alt="NGHA Logo" style="width:92px;height:auto;"></td>
<td style="width:36%;vertical-align:top;text-align:right;direction:rtl;padding:0;line-height:1.18;font-size:11px;color:#000;font-family:\'DejaVu Sans\', sans-serif;">
<div style="font-weight:bold;">المملكة العربية السعودية</div>
<div>وزارة الحرس الوطني</div>
<div>الشؤون الصحية</div>
<div>مدينة الملك عبد العزيز الطبية بجدة</div>
<div>مستشفى الملك خالد الحرس الوطني</div>
</td>
</tr>
</tbody>
</table>';

            $footerContent = '<table style="height:23px;width:74.4898%;border-collapse:collapse;font-family:\'Times New Roman\', serif;font-size:8px;line-height:1.18;color:rgb(0,0,0);margin-left:auto;margin-right:auto;">
<tbody>
<tr style="height:28.3125px;">
<td style="width:37.9738%;vertical-align:top;font-weight:bold;text-align:left;height:28.3125px;">P.O. BOX 9515<br>JEDDAH 21423<br>KINGDOM OF SAUDI ARABIA</td>
<td style="width:23.9585%;vertical-align:top;font-weight:bold;height:28.3125px;text-align:center;">FAX: 624 7444</td>
<td style="width:37.9738%;vertical-align:top;text-align:right;direction:rtl;font-family:\'DejaVu Sans\', sans-serif;font-weight:bold;height:28.3125px;">ص.ب 9515<br>جدة 21423<br>المملكة العربية السعودية</td>
</tr>
</tbody>
</table>';

            $layoutSettings = [
                'fontFamily' => "'Times New Roman', serif",
                'fontSize' => '10.4',
                'border' => ['enabled' => '0', 'width' => '2', 'style' => 'solid', 'color' => '#2f8e55'],
                'frame' => ['style' => 'ngha_green', 'color' => '#2f8e55', 'topInset' => '10', 'sideInset' => '10', 'bottomInset' => '8'],
                'watermark' => ['enabled' => '0', 'text' => null],
                'qrCode' => ['enabled' => '1'],
                'footer' => ['enabled' => '1'],
                'margins' => ['top' => '18', 'right' => '18', 'bottom' => '14', 'left' => '18'],
            ];

            $signatureImage = 'https://i.ibb.co/KpLhP9GV/sign-preview-rev-1.png';
            $stampImage = 'https://i.ibb.co/xSQR4V4D/70917586-5-B59-46-E3-B96-C-5-A68-E3-CFA9-A0.png';

            DB::table('templates')->updateOrInsert(
                ['name' => $templateName],
                [
                    'content' => $bodyContent,
                    'header_content' => $headerContent,
                    'body_content' => $bodyContent,
                    'footer_content' => $footerContent,
                    'signature_name' => 'Dr.Abdulrhman Al Zaharani',
                    'signature_title' => 'Associate Consultant of Emergency Medicine',
                    'signature_image' => $signatureImage,
                    'stamp_image' => $stampImage,
                    'signature_department' => 'King Abdulaziz Medical City-Jeddah',
                    'signature_email' => 'zahraniab13@mngha.med.sa',
                    'layout_settings' => json_encode($layoutSettings),
                    'language' => 'en',
                    'is_active' => true,
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );

            // 2. Ensure "Senior Registrar" and "Fellow" are in the dropdown settings
            $traineeLevelsSetting = DB::table('settings')->where('key', 'dropdownOptions_trainee_level')->first();
            if ($traineeLevelsSetting) {
                $levels = json_decode($traineeLevelsSetting->value, true);
                if (is_array($levels)) {
                    $newLevels = ['Senior Registrar', 'Fellow'];
                    $updated = false;
                    foreach ($newLevels as $level) {
                        if (!in_array($level, $levels)) {
                            $levels[] = $level;
                            $updated = true;
                        }
                    }
                    if ($updated) {
                        DB::table('settings')->where('key', 'dropdownOptions_trainee_level')->update([
                            'value' => json_encode(array_values(array_unique($levels))),
                            'updated_at' => now(),
                        ]);
                    }
                }
            }
        });

        // 3. Clear Caches
        Cache::forget('active_template_v2');
        Cache::forget('all_templates_v2');
        Cache::forget('maintenance_mode');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('templates')->where('name', 'Senior / Fellow Recommendation Letter')->delete();
    }
};
