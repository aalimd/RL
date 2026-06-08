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
        if (!Schema::hasTable('templates') || !Schema::hasTable('settings')) {
            return;
        }

        DB::transaction(function () {
            // 1. Deactivate old templates
            $oldTemplates = [
                'NGHA Emergency Medicine',
                'NGHA Official Framed Recommendation Letter',
                'Senior / Fellow Recommendation Letter'
            ];
            
            DB::table('templates')
                ->whereIn('name', $oldTemplates)
                ->update([
                    'is_active' => false,
                    'updated_at' => now(),
                ]);

            // 2. Add Specialist and Consultant to trainee level dropdown
            $traineeLevelsSetting = DB::table('settings')->where('key', 'dropdownOptions_trainee_level')->first();
            if ($traineeLevelsSetting) {
                $levels = json_decode($traineeLevelsSetting->value, true);
                if (is_array($levels)) {
                    $newLevels = ['Specialist', 'Consultant'];
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

            // Common Template Components
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

            $commonTemplateData = [
                'header_content' => $headerContent,
                'footer_content' => $footerContent,
                'signature_name' => 'Dr.Abdulrhman Al Zaharani',
                'signature_title' => 'Associate Consultant of Emergency Medicine',
                'signature_image' => $signatureImage,
                'stamp_image' => $stampImage,
                'signature_department' => 'King Abdulaziz Medical City-Jeddah',
                'signature_institution' => 'Ministry of National Guard Health Affairs',
                'signature_email' => 'ZahraniAB13@MNGHA.MED.SA',
                'layout_settings' => json_encode($layoutSettings),
                'language' => 'en',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ];

            // 3. Insert new templates
            $templates = [
                [
                    'name' => 'Foundation Recommendation Letter (Students & Interns)',
                    'target_trainee_levels' => json_encode(['Medical Student', 'Intern']),
                    'content' => '<div style="font-family:\'Times New Roman\', serif;font-size:11pt;color:#000;line-height:1.5;">
<h2 style="text-align:center;font-weight:bold;margin:25px 0 25px 0;">Recommendation Letter</h2>
<h3 style="text-align:center;font-weight:bold;margin:0 0 20px 0;">Dr. {{fullName}}</h3>
<p style="margin-bottom:15px;text-align:justify;"><strong>To Whom It May Concern,</strong></p>
<p style="margin-bottom:15px;text-align:justify;">I am writing to express my strong support for <strong>Dr. {{lastName}}\'s</strong> application for {{purpose}}. I had the pleasure of supervising Dr. {{lastName}} during {{his}} {{traineeLevel}} rotation in the {{department}} at {{workLocation}} during {{trainingPeriod}}.</p>
<p style="margin-bottom:15px;text-align:justify;">Throughout {{his}} time with us, Dr. {{lastName}} demonstrated a strong foundation of medical knowledge and an exceptional eagerness to learn. {{He}} was consistently thorough in {{his}} history-taking and physical examinations, presenting cases in a structured and relevant manner. I was particularly impressed by {{his}} dedication to patient care and {{his}} proactive approach to seeking feedback.</p>
<p style="margin-bottom:15px;text-align:justify;">Beyond {{his}} clinical acumen, Dr. {{lastName}} is a consummate professional. The clinical environment can be demanding, but {{he}} maintained a calm, respectful, and collaborative demeanor at all times. {{He}} communicated effectively with patients, families, and the multidisciplinary healthcare team.</p>
<p style="margin-bottom:20px;text-align:justify;">Dr. {{lastName}} is a reliable, hardworking, and compassionate physician who shows immense promise. I recommend {{him}} highly and without reservation for your program.</p>
<p style="margin:0;text-align:left;">Sincerely,</p>
</div>',
                ],
                [
                    'name' => 'Progression Recommendation Letter (Residents)',
                    'target_trainee_levels' => json_encode(['Resident']),
                    'content' => '<div style="font-family:\'Times New Roman\', serif;font-size:11pt;color:#000;line-height:1.5;">
<h2 style="text-align:center;font-weight:bold;margin:25px 0 25px 0;">Recommendation Letter</h2>
<h3 style="text-align:center;font-weight:bold;margin:0 0 20px 0;">Dr. {{fullName}}</h3>
<p style="margin-bottom:15px;text-align:justify;"><strong>To Whom It May Concern,</strong></p>
<p style="margin-bottom:15px;text-align:justify;">It is with great enthusiasm that I recommend <strong>Dr. {{lastName}}</strong> for {{purpose}}. I had the opportunity to work closely with Dr. {{lastName}} while {{he}} served as a {{traineeLevel}} in the {{department}} at {{workLocation}} during {{trainingPeriod}}.</p>
<p style="margin-bottom:15px;text-align:justify;">In the clinical setting, Dr. {{lastName}} demonstrated excellent clinical judgment and a systematic, evidence-based approach to patient care. {{He}} showed great proficiency in formulating sound differential diagnoses and safe management plans. I was particularly impressed by {{his}} ability to handle undifferentiated and complex cases, recognizing {{his}} own limits while demonstrating growing clinical independence.</p>
<p style="margin-bottom:15px;text-align:justify;">Furthermore, Dr. {{lastName}} is an outstanding team player. {{He}} effectively coordinated care with nursing staff and interdisciplinary teams, ensuring smooth patient transitions. {{His}} interactions with patients were consistently marked by empathy, cultural sensitivity, and clear communication regarding treatment plans.</p>
<p style="margin-bottom:20px;text-align:justify;">Dr. {{lastName}} is a highly capable and dedicated physician who will undoubtedly continue to excel in {{his}} medical career. I offer my highest recommendation.</p>
<p style="margin:0;text-align:left;">Sincerely,</p>
</div>',
                ],
                [
                    'name' => 'Leadership Recommendation Letter (Seniors & Fellows)',
                    'target_trainee_levels' => json_encode(['Senior Registrar', 'Fellow']),
                    'content' => '<div style="font-family:\'Times New Roman\', serif;font-size:11pt;color:#000;line-height:1.5;">
<h2 style="text-align:center;font-weight:bold;margin:25px 0 25px 0;">Recommendation Letter</h2>
<h3 style="text-align:center;font-weight:bold;margin:0 0 20px 0;">Dr. {{fullName}}</h3>
<p style="margin-bottom:15px;text-align:justify;"><strong>To Whom It May Concern,</strong></p>
<p style="margin-bottom:15px;text-align:justify;">It is with great pleasure and high regard that I write this letter of recommendation for <strong>Dr. {{lastName}}</strong>. I have had the distinct privilege of working closely with {{him}} during {{his}} tenure as {{a_an}} {{traineeLevel}} in the {{department}} at {{workLocation}} during {{trainingPeriod}}.</p>
<p style="margin-bottom:15px;text-align:justify;">Operating at the level of {{a_an}} {{traineeLevel}} requires exceptional clinical judgment, advanced procedural skills, and the ability to lead and educate junior colleagues. Dr. {{lastName}} consistently exceeded these expectations. {{He}} demonstrated a profound depth of medical knowledge, managing complex, high-acuity cases with confidence and evidence-based practice.</p>
<p style="margin-bottom:15px;text-align:justify;">Beyond {{his}} clinical excellence, Dr. {{lastName}} exhibits outstanding leadership qualities. {{He}} is a natural educator, frequently taking the time to mentor junior staff and foster a collaborative team environment. {{His}} decisive approach and calm presence in high-pressure situations have made {{him}} an invaluable asset to our department.</p>
<p style="margin-bottom:20px;text-align:justify;">I have the utmost confidence in Dr. {{lastName}}\'s abilities and professional trajectory. {{He}} is a highly competent, independent, and compassionate physician. I strongly support {{his}} application for {{purpose}} without reservation.</p>
<p style="margin:0;text-align:left;">Sincerely,</p>
</div>',
                ],
                [
                    'name' => 'Expert Recommendation Letter (Specialists & Consultants)',
                    'target_trainee_levels' => json_encode(['Specialist', 'Consultant']),
                    'content' => '<div style="font-family:\'Times New Roman\', serif;font-size:11pt;color:#000;line-height:1.5;">
<h2 style="text-align:center;font-weight:bold;margin:25px 0 25px 0;">Recommendation Letter</h2>
<h3 style="text-align:center;font-weight:bold;margin:0 0 20px 0;">Dr. {{fullName}}</h3>
<p style="margin-bottom:15px;text-align:justify;"><strong>To Whom It May Concern,</strong></p>
<p style="margin-bottom:15px;text-align:justify;">I am writing to offer my highest recommendation for <strong>Dr. {{lastName}}</strong> in support of {{his}} application for {{purpose}}. I have had the professional privilege of collaborating with Dr. {{lastName}} during {{his}} time as a {{traineeLevel}} in the {{department}} at {{workLocation}}.</p>
<p style="margin-bottom:15px;text-align:justify;">Dr. {{lastName}} is an exceptional clinician who provides expert, state-of-the-art medical care. {{His}} clinical acumen, combined with a meticulous and evidence-based approach to complex patient management, sets a high standard within our institution. {{He}} is highly respected by peers and multidisciplinary teams for {{his}} decisive clinical judgment and collaborative nature.</p>
<p style="margin-bottom:15px;text-align:justify;">In addition to {{his}} clinical duties, Dr. {{lastName}} has been a vital contributor to departmental quality and academic initiatives. {{He}} leads by example, demonstrating an unwavering commitment to patient safety, ethical practice, and the continuous improvement of healthcare delivery.</p>
<p style="margin-bottom:20px;text-align:justify;">Dr. {{lastName}} is an outstanding colleague and a distinguished physician. {{He}} would be a tremendous asset to any premier healthcare institution. I recommend {{him}} with the utmost confidence.</p>
<p style="margin:0;text-align:left;">Sincerely,</p>
</div>',
                ]
            ];

            foreach ($templates as $template) {
                $data = array_merge($commonTemplateData, [
                    'body_content' => $template['content'],
                    'target_trainee_levels' => $template['target_trainee_levels']
                ]);
                
                // Content is usually needed as well for legacy fallback
                $data['content'] = $template['content'];

                DB::table('templates')->updateOrInsert(
                    ['name' => $template['name']],
                    $data
                );
            }
        });

        // 4. Clear Cache
        Cache::forget('active_template_v2');
        Cache::forget('all_templates_v2');
        Cache::forget('maintenance_mode');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Re-activate old templates
        $oldTemplates = [
            'NGHA Emergency Medicine',
            'NGHA Official Framed Recommendation Letter',
            'Senior / Fellow Recommendation Letter'
        ];
        
        DB::table('templates')
            ->whereIn('name', $oldTemplates)
            ->update([
                'is_active' => true,
                'updated_at' => now(),
            ]);

        // Delete new templates
        $newTemplates = [
            'Foundation Recommendation Letter (Students & Interns)',
            'Progression Recommendation Letter (Residents)',
            'Leadership Recommendation Letter (Seniors & Fellows)',
            'Expert Recommendation Letter (Specialists & Consultants)'
        ];

        DB::table('templates')->whereIn('name', $newTemplates)->delete();
        
        Cache::forget('active_template_v2');
        Cache::forget('all_templates_v2');
    }
};
