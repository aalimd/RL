<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::transaction(function () {
            $templates = [
                [
                    'name' => 'Foundation Recommendation Letter (Students & Interns)',
                    'content' => '<div style="font-family:\'Times New Roman\', serif;font-size:10.4pt;color:#000;line-height:1.44;">
<h2 style="text-align:center;font-weight:bold;font-size:12pt;margin:16px 0 14px 0;text-decoration:underline;">Recommendation Letter</h2>
<h3 style="text-align:center;font-weight:bold;font-size:11pt;margin:0 0 24px 0;">Dr. {{fullName}}</h3>
<p style="margin:0px 0px 13px;text-align:center;"><strong>To Whom It May Concern,</strong></p>
<p style="margin:0 0 13px 0;text-align:justify;">I am writing to express my strong support for <strong>Dr. {{lastName}}\'s</strong> application for {{purpose}}. I had the pleasure of supervising <strong>Dr. {{lastName}}</strong> during {{his}} {{traineeLevel}} rotation in the {{department}} at {{workLocation}} during <strong>{{trainingPeriod}}</strong>.</p>
<p style="margin:0 0 13px 0;text-align:justify;">Throughout {{his}} time with us, <strong>Dr. {{lastName}}</strong> demonstrated a strong foundation of medical knowledge and an exceptional eagerness to learn. {{He}} was consistently thorough in {{his}} history-taking and physical examinations, presenting cases in a structured and relevant manner. I was particularly impressed by {{his}} dedication to patient care and {{his}} proactive approach to seeking feedback.</p>
<p style="margin:0 0 13px 0;text-align:justify;">Beyond {{his}} clinical acumen, <strong>Dr. {{lastName}}</strong> is a consummate professional. The clinical environment can be demanding, but {{he}} maintained a calm, respectful, and collaborative demeanor at all times. {{He}} communicated effectively with patients, families, and the multidisciplinary healthcare team.</p>
<p style="margin:0 0 24px 0;text-align:justify;"><strong>Dr. {{lastName}}</strong> is a reliable, hardworking, and compassionate physician who shows immense promise. I recommend {{him}} highly and without reservation for your program.</p>
<p style="margin:0;text-align:left;">Sincerely,</p>
</div>',
                ],
                [
                    'name' => 'Progression Recommendation Letter (Residents)',
                    'content' => '<div style="font-family:\'Times New Roman\', serif;font-size:10.4pt;color:#000;line-height:1.44;">
<h2 style="text-align:center;font-weight:bold;font-size:12pt;margin:16px 0 14px 0;text-decoration:underline;">Recommendation Letter</h2>
<h3 style="text-align:center;font-weight:bold;font-size:11pt;margin:0 0 24px 0;">Dr. {{fullName}}</h3>
<p style="margin:0px 0px 13px;text-align:center;"><strong>To Whom It May Concern,</strong></p>
<p style="margin:0 0 13px 0;text-align:justify;">It is with great enthusiasm that I recommend <strong>Dr. {{lastName}}</strong> for {{purpose}}. I had the opportunity to work closely with <strong>Dr. {{lastName}}</strong> while {{he}} served as a {{traineeLevel}} in the {{department}} at {{workLocation}} during <strong>{{trainingPeriod}}</strong>.</p>
<p style="margin:0 0 13px 0;text-align:justify;">In the clinical setting, <strong>Dr. {{lastName}}</strong> demonstrated excellent clinical judgment and a systematic, evidence-based approach to patient care. {{He}} showed great proficiency in formulating sound differential diagnoses and safe management plans. I was particularly impressed by {{his}} ability to handle undifferentiated and complex cases, recognizing {{his}} own limits while demonstrating growing clinical independence.</p>
<p style="margin:0 0 13px 0;text-align:justify;">Furthermore, <strong>Dr. {{lastName}}</strong> is an outstanding team player. {{He}} effectively coordinated care with nursing staff and interdisciplinary teams, ensuring smooth patient transitions. {{His}} interactions with patients were consistently marked by empathy, cultural sensitivity, and clear communication regarding treatment plans.</p>
<p style="margin:0 0 24px 0;text-align:justify;"><strong>Dr. {{lastName}}</strong> is a highly capable and dedicated physician who will undoubtedly continue to excel in {{his}} medical career. I offer my highest recommendation.</p>
<p style="margin:0;text-align:left;">Sincerely,</p>
</div>',
                ],
                [
                    'name' => 'Leadership Recommendation Letter (Seniors & Fellows)',
                    'content' => '<div style="font-family:\'Times New Roman\', serif;font-size:10.4pt;color:#000;line-height:1.44;">
<h2 style="text-align:center;font-weight:bold;font-size:12pt;margin:16px 0 14px 0;text-decoration:underline;">Recommendation Letter</h2>
<h3 style="text-align:center;font-weight:bold;font-size:11pt;margin:0 0 24px 0;">Dr. {{fullName}}</h3>
<p style="margin:0px 0px 13px;text-align:center;"><strong>To Whom It May Concern,</strong></p>
<p style="margin:0 0 13px 0;text-align:justify;">It is with great pleasure and high regard that I write this letter of recommendation for <strong>Dr. {{lastName}}</strong>. I have had the distinct privilege of working closely with {{him}} during {{his}} tenure as {{a_an}} {{traineeLevel}} in the {{department}} at {{workLocation}} during <strong>{{trainingPeriod}}</strong>.</p>
<p style="margin:0 0 13px 0;text-align:justify;">Operating at the level of {{a_an}} {{traineeLevel}} requires exceptional clinical judgment, advanced procedural skills, and the ability to lead and educate junior colleagues. <strong>Dr. {{lastName}}</strong> consistently exceeded these expectations. {{He}} demonstrated a profound depth of medical knowledge, managing complex, high-acuity cases with confidence and evidence-based practice.</p>
<p style="margin:0 0 13px 0;text-align:justify;">Beyond {{his}} clinical excellence, <strong>Dr. {{lastName}}</strong> exhibits outstanding leadership qualities. {{He}} is a natural educator, frequently taking the time to mentor junior staff and foster a collaborative team environment. {{His}} decisive approach and calm presence in high-pressure situations have made {{him}} an invaluable asset to our department.</p>
<p style="margin:0 0 24px 0;text-align:justify;">I have the utmost confidence in <strong>Dr. {{lastName}}\'s</strong> abilities and professional trajectory. {{He}} is a highly competent, independent, and compassionate physician. I strongly support {{his}} application for {{purpose}} without reservation.</p>
<p style="margin:0;text-align:left;">Sincerely,</p>
</div>',
                ],
                [
                    'name' => 'Expert Recommendation Letter (Specialists & Consultants)',
                    'content' => '<div style="font-family:\'Times New Roman\', serif;font-size:10.4pt;color:#000;line-height:1.44;">
<h2 style="text-align:center;font-weight:bold;font-size:12pt;margin:16px 0 14px 0;text-decoration:underline;">Recommendation Letter</h2>
<h3 style="text-align:center;font-weight:bold;font-size:11pt;margin:0 0 24px 0;">Dr. {{fullName}}</h3>
<p style="margin:0px 0px 13px;text-align:center;"><strong>To Whom It May Concern,</strong></p>
<p style="margin:0 0 13px 0;text-align:justify;">I am writing to offer my highest recommendation for <strong>Dr. {{lastName}}</strong> in support of {{his}} application for {{purpose}}. I have had the professional privilege of collaborating with <strong>Dr. {{lastName}}</strong> during {{his}} time as a {{traineeLevel}} in the {{department}} at {{workLocation}}.</p>
<p style="margin:0 0 13px 0;text-align:justify;"><strong>Dr. {{lastName}}</strong> is an exceptional clinician who provides expert, state-of-the-art medical care. {{His}} clinical acumen, combined with a meticulous and evidence-based approach to complex patient management, sets a high standard within our institution. {{He}} is highly respected by peers and multidisciplinary teams for {{his}} decisive clinical judgment and collaborative nature.</p>
<p style="margin:0 0 13px 0;text-align:justify;">In addition to {{his}} clinical duties, <strong>Dr. {{lastName}}</strong> has been a vital contributor to departmental quality and academic initiatives. {{He}} leads by example, demonstrating an unwavering commitment to patient safety, ethical practice, and the continuous improvement of healthcare delivery.</p>
<p style="margin:0 0 24px 0;text-align:justify;"><strong>Dr. {{lastName}}</strong> is an outstanding colleague and a distinguished physician. {{He}} would be a tremendous asset to any premier healthcare institution. I recommend {{him}} with the utmost confidence.</p>
<p style="margin:0;text-align:left;">Sincerely,</p>
</div>',
                ]
            ];

            foreach ($templates as $template) {
                DB::table('templates')
                    ->where('name', $template['name'])
                    ->update([
                        'body_content' => $template['content'],
                        'content' => $template['content'], // For legacy fallback
                        'updated_at' => now(),
                    ]);
            }
        });

        Cache::forget('active_template_v2');
        Cache::forget('all_templates_v2');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Handled in previous migration
    }
};
