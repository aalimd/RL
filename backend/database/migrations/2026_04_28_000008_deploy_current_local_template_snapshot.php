<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Snapshot generated from the local templates database on 2026-04-28T18:00:06+00:00.
     * Keeps production aligned with the two local templates and form settings.
     */
    private const TEMPLATE_NAMES = array (
  0 => 'NGHA Emergency Medicine',
  1 => 'NGHA Official Framed Recommendation Letter',
);

    public function up(): void
    {
        if (!Schema::hasTable('templates') || !Schema::hasTable('settings')) {
            return;
        }

        DB::transaction(function (): void {
            DB::table('templates')
                ->whereNotIn('name', self::TEMPLATE_NAMES)
                ->update(
                    $this->filterExistingColumns('templates', [
                        'is_active' => false,
                        'updated_at' => now(),
                    ])
                );

            foreach ($this->templateSnapshots() as $template) {
                $name = $template['name'];
                unset($template['id']);

                DB::table('templates')->updateOrInsert(
                    ['name' => $name],
                    $this->filterExistingColumns('templates', $template)
                );
            }

            foreach ($this->settingsSnapshot() as $key => $value) {
                DB::table('settings')->updateOrInsert(
                    ['key' => $key],
                    [
                        'value' => $value,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
            }
        });

        $this->clearRuntimeCache();
    }

    public function down(): void
    {
        $this->clearRuntimeCache();
    }

    private function templateSnapshots(): array
    {
        return array (
  0 => 
  array (
    'name' => 'NGHA Emergency Medicine',
    'content' => '<div style="font-family:\'Times New Roman\', serif;font-size:11pt;color:#000;">
<h2 style="text-align:center;font-weight:bold;margin:25px 0 35px 0;">Dr. {{fullName}}</h2>
<h3 style="margin-bottom:15px;line-height:1.6;text-align:center;"><span style="text-decoration:underline;"><strong>To Whom It May Concern,</strong></span></h3>
<p style="margin-bottom:15px;text-align:justify;line-height:1.6;">This letter is to certify that <strong>Dr. {{lastName}}</strong> completed a rotation in the Emergency Department at King Abdulaziz Medical City, Jeddah (MNGHA) during <strong>{{trainingPeriod}}</strong> as part of {{his}} medical internship.</p>
<p style="margin-bottom:15px;text-align:justify;line-height:1.6;">Throughout {{his}} rotation, <strong>Dr. {{lastName}}</strong> demonstrated solid medical knowledge and a consistently professional attitude. {{He}} was diligent, dependable, and showed a clear commitment to learning and patient care. {{He}} interacted effectively with patients, residents, consultants, nursing staff, and other members of the healthcare team.</p>
<p style="margin-bottom:15px;text-align:justify;line-height:1.6;"><strong>Dr. {{lastName}}</strong> displayed particular interest in Emergency Medicine, with good situational awareness, appropriate prioritization, and the ability to work efficiently in a fast-paced environment. {{He}} was receptive to feedback and showed continuous improvement during {{his}} time in the department.</p>
<p style="margin-bottom:20px;text-align:justify;line-height:1.6;">Based on {{his}} performance, work ethic, and interpersonal skills, I believe <strong>Dr. {{lastName}}</strong> would be a valuable addition to any training program or institution {{he}} joins. I recommend {{him}} without reservation for the specialty {{he}} chooses to pursue.</p>
</div>',
    'header_content' => '<table style="width:100%;border-collapse:collapse;border:none;font-family:\'Times New Roman\', serif;">
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
</table>',
    'body_content' => '<div style="font-family:\'Times New Roman\', serif;font-size:11pt;color:#000;">
<h2 style="text-align:center;font-weight:bold;margin:25px 0 35px 0;">Dr. {{fullName}}</h2>
<h3 style="margin-bottom:15px;line-height:1.6;text-align:center;"><span style="text-decoration:underline;"><strong>To Whom It May Concern,</strong></span></h3>
<p style="margin-bottom:15px;text-align:justify;line-height:1.6;">This letter is to certify that <strong>Dr. {{lastName}}</strong> completed a rotation in the Emergency Department at King Abdulaziz Medical City, Jeddah (MNGHA) during <strong>{{trainingPeriod}}</strong> as part of {{his}} medical internship.</p>
<p style="margin-bottom:15px;text-align:justify;line-height:1.6;">Throughout {{his}} rotation, <strong>Dr. {{lastName}}</strong> demonstrated solid medical knowledge and a consistently professional attitude. {{He}} was diligent, dependable, and showed a clear commitment to learning and patient care. {{He}} interacted effectively with patients, residents, consultants, nursing staff, and other members of the healthcare team.</p>
<p style="margin-bottom:15px;text-align:justify;line-height:1.6;"><strong>Dr. {{lastName}}</strong> displayed particular interest in Emergency Medicine, with good situational awareness, appropriate prioritization, and the ability to work efficiently in a fast-paced environment. {{He}} was receptive to feedback and showed continuous improvement during {{his}} time in the department.</p>
<p style="margin-bottom:20px;text-align:justify;line-height:1.6;">Based on {{his}} performance, work ethic, and interpersonal skills, I believe <strong>Dr. {{lastName}}</strong> would be a valuable addition to any training program or institution {{he}} joins. I recommend {{him}} without reservation for the specialty {{he}} chooses to pursue.</p>
</div>',
    'footer_content' => '<table style="height:43px;width:71.243%;border-collapse:collapse;font-family:\'Times New Roman\', serif;font-size:8px;line-height:1.18;color:rgb(0,0,0);margin-left:auto;margin-right:auto;">
<tbody>
<tr>
<td style="width:37.9759%;vertical-align:top;text-align:left;font-weight:bold;">P.O. BOX 9515<br>JEDDAH 21423<br>KINGDOM OF SAUDI ARABIA</td>
<td style="width:23.9554%;vertical-align:top;text-align:center;font-weight:bold;">FAX: 624 7444</td>
<td style="width:37.9759%;vertical-align:top;text-align:right;direction:rtl;font-family:\'DejaVu Sans\', sans-serif;font-weight:bold;">ص.ب 9515<br>جدة 21423<br>المملكة العربية السعودية</td>
</tr>
</tbody>
</table>',
    'signature_name' => 'Dr.Abdulrhman Al Zaharani',
    'signature_title' => 'ASSOCIATE CONSULTANT EMERGENCY MEDICINE',
    'signature_image' => 'https://i.ibb.co/KpLhP9GV/sign-preview-rev-1.png',
    'stamp_image' => 'https://chatgpt.com/backend-api/estuary/public_content/enc/eyJpZCI6Im1fNjllOTIzOTJmYjNjODE5MThmZjQ2NmEyMTljMzFhMDI6ZmlsZV8wMDAwMDAwMGIxMjg3MWY0OTgyMmFlNTg4NDMxZGE4OSIsInRzIjoiMjA1NjUiLCJwIjoicHlpIiwiY2lkIjoiMSIsInNpZyI6IjEyYWZhMjdhMjc4YmEyNjdkZTRjYThhN2FjNGU0NzA3ZjM0OTdkMGZjMmE1NTdiZjE2YWM2YjhmMjJhNDVmMTUiLCJ2IjoiMCIsImdpem1vX2lkIjpudWxsLCJjcyI6bnVsbCwiY2RuIjpudWxsLCJjcCI6bnVsbCwibWEiOm51bGx9',
    'signature_institution' => 'Ministry of National Guard Health Affairs',
    'signature_department' => 'King Abdulaziz Medical City-Jeddah',
    'signature_email' => 'ZahraniAB13@MNGHA.MED.SA',
    'signature_phone' => NULL,
    'layout_settings' => '{"fontFamily":"\'Times New Roman\', serif","fontSize":"10.4","border":{"enabled":"0","width":"2","style":"solid","color":"#2f8e55"},"frame":{"style":"ngha_green","color":"#2f8e55","topInset":"10","sideInset":"10","bottomInset":"8"},"watermark":{"enabled":"0","text":null},"qrCode":{"enabled":"1"},"footer":{"enabled":"1"},"margins":{"top":"18","right":"18","bottom":"14","left":"18"}}',
    'draft_data' => NULL,
    'last_draft_saved_at' => NULL,
    'reset_data' => '{"name":"NGHA Emergency Medicine","header_content":"<table style=\\"width:100%;border-collapse:collapse;border:none;font-family:\'Times New Roman\', serif;\\">\\n<tbody>\\n<tr>\\n<td style=\\"width:36%;vertical-align:top;text-align:left;padding:0;line-height:1.15;font-size:10px;color:#000;\\">\\n<div style=\\"font-weight:bold;\\">Kingdom of Saudi Arabia</div>\\n<div>National Guard</div>\\n<div>Health Affairs</div>\\n<div>King Abdulaziz Medical City - Jeddah</div>\\n<div>King Khalid National Guard Hospital</div>\\n<div style=\\"height:5px;\\"></div>\\n<div style=\\"color:#b11f24;font-weight:bold;\\">Department of Emergency Medicine</div>\\n<div style=\\"color:#b11f24;font-weight:bold;\\">Tel: 012-2266666</div>\\n<div style=\\"color:#b11f24;font-weight:bold;\\">Ext: 62790-62791</div>\\n<div style=\\"color:#b11f24;font-weight:bold;\\">Email: emerg@mngha.med.sa</div>\\n</td>\\n<td style=\\"width:28%;vertical-align:top;text-align:center;padding:0;\\"><img src=\\"https://i.ibb.co/JW3Q0t7Y/mnghalogo.png\\" alt=\\"NGHA Logo\\" style=\\"width:92px;height:auto;\\"></td>\\n<td style=\\"width:36%;vertical-align:top;text-align:right;direction:rtl;padding:0;line-height:1.18;font-size:11px;color:#000;font-family:\'DejaVu Sans\', sans-serif;\\">\\n<div style=\\"font-weight:bold;\\">المملكة العربية السعودية</div>\\n<div>وزارة الحرس الوطني</div>\\n<div>الشؤون الصحية</div>\\n<div>مدينة الملك عبد العزيز الطبية بجدة</div>\\n<div>مستشفى الملك خالد الحرس الوطني</div>\\n</td>\\n</tr>\\n</tbody>\\n</table>","body_content":"<div style=\\"font-family:\'Times New Roman\', serif;font-size:11pt;color:#000;\\">\\n<h2 style=\\"text-align:center;font-weight:bold;margin:25px 0 35px 0;\\">Dr. {{fullName}}</h2>\\n<h3 style=\\"margin-bottom:15px;line-height:1.6;text-align:center;\\"><span style=\\"text-decoration:underline;\\"><strong>To Whom It May Concern,</strong></span></h3>\\n<p style=\\"margin-bottom:15px;text-align:justify;line-height:1.6;\\">This letter is to certify that <strong>Dr. {{lastName}}</strong> completed a rotation in the Emergency Department at King Abdulaziz Medical City, Jeddah (MNGHA) during <strong>{{trainingPeriod}}</strong> as part of {{his}} medical internship.</p>\\n<p style=\\"margin-bottom:15px;text-align:justify;line-height:1.6;\\">Throughout {{his}} rotation, <strong>Dr. {{lastName}}</strong> demonstrated solid medical knowledge and a consistently professional attitude. {{He}} was diligent, dependable, and showed a clear commitment to learning and patient care. {{He}} interacted effectively with patients, residents, consultants, nursing staff, and other members of the healthcare team.</p>\\n<p style=\\"margin-bottom:15px;text-align:justify;line-height:1.6;\\"><strong>Dr. {{lastName}}</strong> displayed particular interest in Emergency Medicine, with good situational awareness, appropriate prioritization, and the ability to work efficiently in a fast-paced environment. {{He}} was receptive to feedback and showed continuous improvement during {{his}} time in the department.</p>\\n<p style=\\"margin-bottom:20px;text-align:justify;line-height:1.6;\\">Based on {{his}} performance, work ethic, and interpersonal skills, I believe <strong>Dr. {{lastName}}</strong> would be a valuable addition to any training program or institution {{he}} joins. I recommend {{him}} without reservation for the specialty {{he}} chooses to pursue.</p>\\n</div>","footer_content":"<table style=\\"height:43px;width:71.243%;border-collapse:collapse;font-family:\'Times New Roman\', serif;font-size:8px;line-height:1.18;color:rgb(0,0,0);margin-left:auto;margin-right:auto;\\">\\n<tbody>\\n<tr>\\n<td style=\\"width:37.9759%;vertical-align:top;text-align:left;font-weight:bold;\\">P.O. BOX 9515<br>JEDDAH 21423<br>KINGDOM OF SAUDI ARABIA</td>\\n<td style=\\"width:23.9554%;vertical-align:top;text-align:center;font-weight:bold;\\">FAX: 624 7444</td>\\n<td style=\\"width:37.9759%;vertical-align:top;text-align:right;direction:rtl;font-family:\'DejaVu Sans\', sans-serif;font-weight:bold;\\">ص.ب 9515<br>جدة 21423<br>المملكة العربية السعودية</td>\\n</tr>\\n</tbody>\\n</table>","content":"<div style=\\"font-family:\'Times New Roman\', serif;font-size:11pt;color:#000;\\">\\n<h2 style=\\"text-align:center;font-weight:bold;margin:25px 0 35px 0;\\">Dr. {{fullName}}</h2>\\n<h3 style=\\"margin-bottom:15px;line-height:1.6;text-align:center;\\"><span style=\\"text-decoration:underline;\\"><strong>To Whom It May Concern,</strong></span></h3>\\n<p style=\\"margin-bottom:15px;text-align:justify;line-height:1.6;\\">This letter is to certify that <strong>Dr. {{lastName}}</strong> completed a rotation in the Emergency Department at King Abdulaziz Medical City, Jeddah (MNGHA) during <strong>{{trainingPeriod}}</strong> as part of {{his}} medical internship.</p>\\n<p style=\\"margin-bottom:15px;text-align:justify;line-height:1.6;\\">Throughout {{his}} rotation, <strong>Dr. {{lastName}}</strong> demonstrated solid medical knowledge and a consistently professional attitude. {{He}} was diligent, dependable, and showed a clear commitment to learning and patient care. {{He}} interacted effectively with patients, residents, consultants, nursing staff, and other members of the healthcare team.</p>\\n<p style=\\"margin-bottom:15px;text-align:justify;line-height:1.6;\\"><strong>Dr. {{lastName}}</strong> displayed particular interest in Emergency Medicine, with good situational awareness, appropriate prioritization, and the ability to work efficiently in a fast-paced environment. {{He}} was receptive to feedback and showed continuous improvement during {{his}} time in the department.</p>\\n<p style=\\"margin-bottom:20px;text-align:justify;line-height:1.6;\\">Based on {{his}} performance, work ethic, and interpersonal skills, I believe <strong>Dr. {{lastName}}</strong> would be a valuable addition to any training program or institution {{he}} joins. I recommend {{him}} without reservation for the specialty {{he}} chooses to pursue.</p>\\n</div>","signature_name":"Dr.Abdulrhman Al Zaharani","signature_title":"ASSOCIATE CONSULTANT EMERGENCY MEDICINE","signature_department":"King Abdulaziz Medical City-Jeddah","signature_institution":"Ministry of National Guard Health Affairs","signature_email":"ZahraniAB13@MNGHA.MED.SA","signature_phone":null,"signature_image":"https://i.ibb.co/KpLhP9GV/sign-preview-rev-1.png","stamp_image":"https://chatgpt.com/backend-api/estuary/public_content/enc/eyJpZCI6Im1fNjllOTIzOTJmYjNjODE5MThmZjQ2NmEyMTljMzFhMDI6ZmlsZV8wMDAwMDAwMGIxMjg3MWY0OTgyMmFlNTg4NDMxZGE4OSIsInRzIjoiMjA1NjUiLCJwIjoicHlpIiwiY2lkIjoiMSIsInNpZyI6IjEyYWZhMjdhMjc4YmEyNjdkZTRjYThhN2FjNGU0NzA3ZjM0OTdkMGZjMmE1NTdiZjE2YWM2YjhmMjJhNDVmMTUiLCJ2IjoiMCIsImdpem1vX2lkIjpudWxsLCJjcyI6bnVsbCwiY2RuIjpudWxsLCJjcCI6bnVsbCwibWEiOm51bGx9","language":"en","is_active":true,"layout_settings":{"fontFamily":"\'Times New Roman\', serif","fontSize":"10.4","border":{"enabled":"0","width":"2","style":"solid","color":"#2f8e55"},"frame":{"style":"ngha_green","color":"#2f8e55","topInset":"10","sideInset":"10","bottomInset":"8"},"watermark":{"enabled":"0","text":null},"qrCode":{"enabled":"1"},"footer":{"enabled":"1"},"margins":{"top":"18","right":"18","bottom":"14","left":"18"}}}',
    'reset_saved_at' => '2026-04-28 16:55:53',
    'language' => 'en',
    'is_active' => 1,
    'created_at' => '2026-01-07 19:03:59',
    'updated_at' => '2026-04-28 16:50:30',
    'deleted_at' => NULL,
  ),
  1 => 
  array (
    'name' => 'NGHA Official Framed Recommendation Letter',
    'content' => '<div style="font-family:\'Times New Roman\', serif;font-size:10.4pt;color:#000;line-height:1.44;">
<h2 style="text-align:center;font-weight:bold;font-size:12pt;margin:16px 0 14px 0;text-decoration:underline;">Recommendation Letter</h2>
<h3 style="text-align:center;font-weight:bold;font-size:11pt;margin:0 0 24px 0;">Dr. {{fullName}}</h3>
<p style="margin:0px 0px 13px;text-align:center;"><strong>To Whom It May Concern,</strong></p>
<p style="margin:0 0 13px 0;text-align:justify;">I am writing to express my strong support for <strong>Dr. {{lastName}}\'s</strong> application to your residency program. I had the opportunity to supervise and evaluate <strong>Dr. {{lastName}}</strong> during {{his}} Emergency Department intern rotation in <strong>{{trainingPeriod}}</strong>. Over the course of the month, {{he}} proved to be a dedicated, well-rounded, and highly capable young physician.</p>
<p style="margin:0 0 13px 0;text-align:justify;">In the clinical setting, <strong>Dr. {{lastName}}</strong> demonstrated a solid foundation of medical knowledge and a systematic approach to patient care. {{He}} was consistently thorough in {{his}} history-taking and physical examinations, and {{his}} case presentations were organized and relevant. I was particularly impressed by {{his}} safe approach to undifferentiated patients; {{he}} recognized {{his}} own limits, knew exactly when to ask for attending input, and formulated sound differential diagnoses.</p>
<p style="margin:0 0 13px 0;text-align:justify;">Beyond {{his}} clinical skills, <strong>Dr. {{lastName}}</strong> is a consummate professional. The ED environment can be incredibly stressful, but {{he}} always maintained a calm and respectful demeanor. {{He}} communicates effectively not only with patients and their families, ensuring they understand their care plans, but also with the nursing staff and our interdisciplinary teams.</p>
<p style="margin:0 0 24px 0;text-align:justify;"><strong>Dr. {{lastName}}</strong> is a reliable, hardworking, and compassionate physician who will undoubtedly thrive in residency training. I recommend {{him}} highly and without reservation.</p>
<p style="margin:0;text-align:left;">Sincerely,</p>
</div>',
    'header_content' => '<table style="width:100%;border-collapse:collapse;border:none;font-family:\'Times New Roman\', serif;">
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
</table>',
    'body_content' => '<div style="font-family:\'Times New Roman\', serif;font-size:10.4pt;color:#000;line-height:1.44;">
<h2 style="text-align:center;font-weight:bold;font-size:12pt;margin:16px 0 14px 0;text-decoration:underline;">Recommendation Letter</h2>
<h3 style="text-align:center;font-weight:bold;font-size:11pt;margin:0 0 24px 0;">Dr. {{fullName}}</h3>
<p style="margin:0px 0px 13px;text-align:center;"><strong>To Whom It May Concern,</strong></p>
<p style="margin:0 0 13px 0;text-align:justify;">I am writing to express my strong support for <strong>Dr. {{lastName}}\'s</strong> application to your residency program. I had the opportunity to supervise and evaluate <strong>Dr. {{lastName}}</strong> during {{his}} Emergency Department intern rotation in <strong>{{trainingPeriod}}</strong>. Over the course of the month, {{he}} proved to be a dedicated, well-rounded, and highly capable young physician.</p>
<p style="margin:0 0 13px 0;text-align:justify;">In the clinical setting, <strong>Dr. {{lastName}}</strong> demonstrated a solid foundation of medical knowledge and a systematic approach to patient care. {{He}} was consistently thorough in {{his}} history-taking and physical examinations, and {{his}} case presentations were organized and relevant. I was particularly impressed by {{his}} safe approach to undifferentiated patients; {{he}} recognized {{his}} own limits, knew exactly when to ask for attending input, and formulated sound differential diagnoses.</p>
<p style="margin:0 0 13px 0;text-align:justify;">Beyond {{his}} clinical skills, <strong>Dr. {{lastName}}</strong> is a consummate professional. The ED environment can be incredibly stressful, but {{he}} always maintained a calm and respectful demeanor. {{He}} communicates effectively not only with patients and their families, ensuring they understand their care plans, but also with the nursing staff and our interdisciplinary teams.</p>
<p style="margin:0 0 24px 0;text-align:justify;"><strong>Dr. {{lastName}}</strong> is a reliable, hardworking, and compassionate physician who will undoubtedly thrive in residency training. I recommend {{him}} highly and without reservation.</p>
<p style="margin:0;text-align:left;">Sincerely,</p>
</div>',
    'footer_content' => '<table style="height:23px;width:74.4898%;border-collapse:collapse;font-family:\'Times New Roman\', serif;font-size:8px;line-height:1.18;color:rgb(0,0,0);margin-left:auto;margin-right:auto;">
<tbody>
<tr style="height:28.3125px;">
<td style="width:37.9738%;vertical-align:top;font-weight:bold;text-align:left;height:28.3125px;">P.O. BOX 9515<br>JEDDAH 21423<br>KINGDOM OF SAUDI ARABIA</td>
<td style="width:23.9585%;vertical-align:top;font-weight:bold;height:28.3125px;text-align:center;">FAX: 624 7444</td>
<td style="width:37.9738%;vertical-align:top;text-align:right;direction:rtl;font-family:\'DejaVu Sans\', sans-serif;font-weight:bold;height:28.3125px;">ص.ب 9515<br>جدة 21423<br>المملكة العربية السعودية</td>
</tr>
</tbody>
</table>',
    'signature_name' => 'Dr.Abdulrhman Al Zaharani',
    'signature_title' => 'ASSOCIATE CONSULTANT EMERGENCY MEDICINE',
    'signature_image' => 'https://i.ibb.co/KpLhP9GV/sign-preview-rev-1.png',
    'stamp_image' => NULL,
    'signature_institution' => 'Ministry of National Guard Health Affairs',
    'signature_department' => 'King Abdulaziz Medical City-Jeddah',
    'signature_email' => 'ZahraniAB13@MNGHA.MED.SA',
    'signature_phone' => NULL,
    'layout_settings' => '{"fontFamily":"\'Times New Roman\', serif","fontSize":"10.4","border":{"enabled":"0","width":"2","style":"solid","color":"#2f8e55"},"frame":{"style":"ngha_green","color":"#2f8e55","topInset":"10","sideInset":"10","bottomInset":"8"},"watermark":{"enabled":"0","text":null},"qrCode":{"enabled":"1"},"footer":{"enabled":"1"},"margins":{"top":"18","right":"18","bottom":"14","left":"18"}}',
    'draft_data' => NULL,
    'last_draft_saved_at' => NULL,
    'reset_data' => '{"name":"NGHA Official Framed Recommendation Letter","header_content":"<table style=\\"width:100%;border-collapse:collapse;border:none;font-family:\'Times New Roman\', serif;\\">\\n<tbody>\\n<tr>\\n<td style=\\"width:36%;vertical-align:top;text-align:left;padding:0;line-height:1.15;font-size:10px;color:#000;\\">\\n<div style=\\"font-weight:bold;\\">Kingdom of Saudi Arabia</div>\\n<div>National Guard</div>\\n<div>Health Affairs</div>\\n<div>King Abdulaziz Medical City - Jeddah</div>\\n<div>King Khalid National Guard Hospital</div>\\n<div style=\\"height:5px;\\"></div>\\n<div style=\\"color:#b11f24;font-weight:bold;\\">Department of Emergency Medicine</div>\\n<div style=\\"color:#b11f24;font-weight:bold;\\">Tel: 012-2266666</div>\\n<div style=\\"color:#b11f24;font-weight:bold;\\">Ext: 62790-62791</div>\\n<div style=\\"color:#b11f24;font-weight:bold;\\">Email: emerg@mngha.med.sa</div>\\n</td>\\n<td style=\\"width:28%;vertical-align:top;text-align:center;padding:0;\\"><img src=\\"https://i.ibb.co/JW3Q0t7Y/mnghalogo.png\\" alt=\\"NGHA Logo\\" style=\\"width:92px;height:auto;\\"></td>\\n<td style=\\"width:36%;vertical-align:top;text-align:right;direction:rtl;padding:0;line-height:1.18;font-size:11px;color:#000;font-family:\'DejaVu Sans\', sans-serif;\\">\\n<div style=\\"font-weight:bold;\\">المملكة العربية السعودية</div>\\n<div>وزارة الحرس الوطني</div>\\n<div>الشؤون الصحية</div>\\n<div>مدينة الملك عبد العزيز الطبية بجدة</div>\\n<div>مستشفى الملك خالد الحرس الوطني</div>\\n</td>\\n</tr>\\n</tbody>\\n</table>","body_content":"<div style=\\"font-family:\'Times New Roman\', serif;font-size:10.4pt;color:#000;line-height:1.44;\\">\\n<h2 style=\\"text-align:center;font-weight:bold;font-size:12pt;margin:16px 0 14px 0;text-decoration:underline;\\">Recommendation Letter</h2>\\n<h3 style=\\"text-align:center;font-weight:bold;font-size:11pt;margin:0 0 24px 0;\\">Dr. {{fullName}}</h3>\\n<p style=\\"margin:0px 0px 13px;text-align:center;\\"><strong>To Whom It May Concern,</strong></p>\\n<p style=\\"margin:0 0 13px 0;text-align:justify;\\">I am writing to express my strong support for <strong>Dr. {{lastName}}\'s</strong> application to your residency program. I had the opportunity to supervise and evaluate <strong>Dr. {{lastName}}</strong> during {{his}} Emergency Department intern rotation in <strong>{{trainingPeriod}}</strong>. Over the course of the month, {{he}} proved to be a dedicated, well-rounded, and highly capable young physician.</p>\\n<p style=\\"margin:0 0 13px 0;text-align:justify;\\">In the clinical setting, <strong>Dr. {{lastName}}</strong> demonstrated a solid foundation of medical knowledge and a systematic approach to patient care. {{He}} was consistently thorough in {{his}} history-taking and physical examinations, and {{his}} case presentations were organized and relevant. I was particularly impressed by {{his}} safe approach to undifferentiated patients; {{he}} recognized {{his}} own limits, knew exactly when to ask for attending input, and formulated sound differential diagnoses.</p>\\n<p style=\\"margin:0 0 13px 0;text-align:justify;\\">Beyond {{his}} clinical skills, <strong>Dr. {{lastName}}</strong> is a consummate professional. The ED environment can be incredibly stressful, but {{he}} always maintained a calm and respectful demeanor. {{He}} communicates effectively not only with patients and their families, ensuring they understand their care plans, but also with the nursing staff and our interdisciplinary teams.</p>\\n<p style=\\"margin:0 0 24px 0;text-align:justify;\\"><strong>Dr. {{lastName}}</strong> is a reliable, hardworking, and compassionate physician who will undoubtedly thrive in residency training. I recommend {{him}} highly and without reservation.</p>\\n<p style=\\"margin:0;text-align:left;\\">Sincerely,</p>\\n</div>","footer_content":"<table style=\\"height:23px;width:74.4898%;border-collapse:collapse;font-family:\'Times New Roman\', serif;font-size:8px;line-height:1.18;color:rgb(0,0,0);margin-left:auto;margin-right:auto;\\">\\n<tbody>\\n<tr style=\\"height:28.3125px;\\">\\n<td style=\\"width:37.9738%;vertical-align:top;font-weight:bold;text-align:left;height:28.3125px;\\">P.O. BOX 9515<br>JEDDAH 21423<br>KINGDOM OF SAUDI ARABIA</td>\\n<td style=\\"width:23.9585%;vertical-align:top;font-weight:bold;height:28.3125px;text-align:center;\\">FAX: 624 7444</td>\\n<td style=\\"width:37.9738%;vertical-align:top;text-align:right;direction:rtl;font-family:\'DejaVu Sans\', sans-serif;font-weight:bold;height:28.3125px;\\">ص.ب 9515<br>جدة 21423<br>المملكة العربية السعودية</td>\\n</tr>\\n</tbody>\\n</table>","content":"<div style=\\"font-family:\'Times New Roman\', serif;font-size:10.4pt;color:#000;line-height:1.44;\\">\\n<h2 style=\\"text-align:center;font-weight:bold;font-size:12pt;margin:16px 0 14px 0;text-decoration:underline;\\">Recommendation Letter</h2>\\n<h3 style=\\"text-align:center;font-weight:bold;font-size:11pt;margin:0 0 24px 0;\\">Dr. {{fullName}}</h3>\\n<p style=\\"margin:0px 0px 13px;text-align:center;\\"><strong>To Whom It May Concern,</strong></p>\\n<p style=\\"margin:0 0 13px 0;text-align:justify;\\">I am writing to express my strong support for <strong>Dr. {{lastName}}\'s</strong> application to your residency program. I had the opportunity to supervise and evaluate <strong>Dr. {{lastName}}</strong> during {{his}} Emergency Department intern rotation in <strong>{{trainingPeriod}}</strong>. Over the course of the month, {{he}} proved to be a dedicated, well-rounded, and highly capable young physician.</p>\\n<p style=\\"margin:0 0 13px 0;text-align:justify;\\">In the clinical setting, <strong>Dr. {{lastName}}</strong> demonstrated a solid foundation of medical knowledge and a systematic approach to patient care. {{He}} was consistently thorough in {{his}} history-taking and physical examinations, and {{his}} case presentations were organized and relevant. I was particularly impressed by {{his}} safe approach to undifferentiated patients; {{he}} recognized {{his}} own limits, knew exactly when to ask for attending input, and formulated sound differential diagnoses.</p>\\n<p style=\\"margin:0 0 13px 0;text-align:justify;\\">Beyond {{his}} clinical skills, <strong>Dr. {{lastName}}</strong> is a consummate professional. The ED environment can be incredibly stressful, but {{he}} always maintained a calm and respectful demeanor. {{He}} communicates effectively not only with patients and their families, ensuring they understand their care plans, but also with the nursing staff and our interdisciplinary teams.</p>\\n<p style=\\"margin:0 0 24px 0;text-align:justify;\\"><strong>Dr. {{lastName}}</strong> is a reliable, hardworking, and compassionate physician who will undoubtedly thrive in residency training. I recommend {{him}} highly and without reservation.</p>\\n<p style=\\"margin:0;text-align:left;\\">Sincerely,</p>\\n</div>","signature_name":"Dr.Abdulrhman Al Zaharani","signature_title":"ASSOCIATE CONSULTANT EMERGENCY MEDICINE","signature_department":"King Abdulaziz Medical City-Jeddah","signature_institution":"Ministry of National Guard Health Affairs","signature_email":"ZahraniAB13@MNGHA.MED.SA","signature_phone":null,"signature_image":"https://i.ibb.co/KpLhP9GV/sign-preview-rev-1.png","stamp_image":null,"language":"en","is_active":true,"layout_settings":{"fontFamily":"\'Times New Roman\', serif","fontSize":"10.4","border":{"enabled":"0","width":"2","style":"solid","color":"#2f8e55"},"frame":{"style":"ngha_green","color":"#2f8e55","topInset":"10","sideInset":"10","bottomInset":"8"},"watermark":{"enabled":"0","text":null},"qrCode":{"enabled":"1"},"footer":{"enabled":"1"},"margins":{"top":"18","right":"18","bottom":"14","left":"18"}}}',
    'reset_saved_at' => '2026-04-28 16:55:53',
    'language' => 'en',
    'is_active' => 1,
    'created_at' => '2026-04-28 14:40:23',
    'updated_at' => '2026-04-28 16:48:14',
    'deleted_at' => NULL,
  ),
);
    }

    private function settingsSnapshot(): array
    {
        return array (
  'templateSelectionMode' => 'student_choice',
  'defaultTemplateId' => '',
  'studentTemplateIds' => '[]',
  'allowCustomContent' => 'false',
  'formFieldConfig' => '{"student_name":{"visible":true,"required":true},"middle_name":{"visible":true,"required":false},"last_name":{"visible":true,"required":true},"gender":{"visible":true,"required":true},"student_email":{"visible":true,"required":true},"university":{"visible":true,"required":false},"verification_token":{"visible":true,"required":true},"training_period":{"visible":true,"required":true},"phone":{"visible":true,"required":false},"major":{"visible":false,"required":false},"purpose":{"visible":true,"required":false},"deadline":{"visible":true,"required":true},"notes":{"visible":false,"required":false}}',
);
    }

    private function filterExistingColumns(string $table, array $values): array
    {
        return array_filter(
            $values,
            static fn (string $column): bool => Schema::hasColumn($table, $column),
            ARRAY_FILTER_USE_KEY
        );
    }

    private function clearRuntimeCache(): void
    {
        Cache::forget('active_template_v2');
        Cache::forget('all_templates_v2');
        Cache::forget('maintenance_mode');
    }
};
