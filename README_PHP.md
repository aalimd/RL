# 🚀 دليل التثبيت الشامل (Medical Recommendation System)

هذا الدليل يشرح كيفية تشغيل النظام على جهازك المحلي (XAMPP) وكيفية رفعه وتشغيله على استضافة Hostinger بالتفصيل الممل.

---

## 💻 أولاً: التشغيل المحلي (XAMPP)

استخدام XAMPP فكرة ممتازة للتجربة قبل الرفع.

### 1. تجهيز قاعدة البيانات
1. افتح **XAMPP Control Panel** وشغل `Apache` و `MySQL`.
2. اذهب إلى `http://localhost/phpmyadmin`.
3. أنشئ قاعدة بيانات جديدة باسم `recom99`.

### 2. تجهيز الباك اند (Backend)
1. اذهب لمجلد `htdocs` داخل مجلد XAMPP (عادة في `C:\xampp\htdocs` أو `/Applications/XAMPP/xamppfiles/htdocs`).
2. أنشئ مجلد باسم `recom99`.
3. انسخ محتويات مجلد `backend` الخاص بنا إلى داخل `recom99`.
4. **تثبيت المكتبات:**
   - ستحتاج لتثبيت `Composer` على جهازك إذا لم يكن مثبتاً.
   - افتح الطرفية (Terminal/CMD) داخل مجلد `recom99` واكتب:
     ```bash
     composer install
     ```
   - *ملاحظة:* إذا لم تستطع تشغيل هذا الأمر، لن يعمل المشروع لأن ملفات `vendor` (نواة Laravel) ناقصة.
5. **إعداد البيئة:**
   - انسخ ملف `.env.example` وسمّه `.env`.
   - افتحه وعدل بيانات الاتصال:
     ```ini
     DB_DATABASE=recom99
     DB_USERNAME=root
     DB_PASSWORD=
     ```
6. **بناء الجداول:**
   - في الطرفية شغل الأمر: `php artisan migrate`

### 3. تشغيل الفرونت اند (Frontend)
1. من مجلد المشروع الأصلي (الذي فيه واجهة React)، افتح ملف `client/src/api.js`.
2. تأكد أن الرابط يشير لسيرفر XAMPP:
   ```javascript
   baseURL: 'http://localhost/recom99/public/api',
   ```
3. شغل المشروع كالمعتاد: `npm run dev`.

---

## 🌐 ثانياً: التثبيت على Hostinger (الوضع النهائي)

### الخطوة 1: تجهيز قاعدة البيانات
1. ادخل لوحة تحكم Hostinger -> **Databases**.
2. أنشئ قاعدة بيانات جديدة.
   - اسم القاعدة: (احفظه، مثلاً `u123_recom99`)
   - اسم المستخدم: (احفظه، مثلاً `u123_admin`)
   - كلمة المرور: (احفظها جيداً)

### الخطوة 2: رفع ملفات الباك اند (Backend)
1. اذهب إلى **File Manager**.
2. يُفضل إنشاء مجلد **خارج** `public_html` لزيادة الأمان، سمّه `laravel_core`.
3. ارفع جميع ملفات مجلد `backend` إلى داخل `laravel_core`.
4. **تثبيت المكتبات (Composer):**
   - في لوحة Hostinger، اذهب لـ **Advanced** -> **SSH Access** (إذا كانت باقتك تدعم ذلك).
   - اتصل بالترمينال واذهب للمجلد: `cd laravel_core`
   - اكتب: `composer install --no-dev --optimize-autoloader`
   - مهم: هذا الإصدار مهيأ ليعمل على PHP `8.2.30+`. إذا ظهر خطأ أن Composer يحتاج PHP `8.3` فهذا يعني أن مجلد `vendor` قديم ويجب إعادة تثبيته من ملف `composer.lock` الحالي.
   - *بديل:* إذا لم يتوفر SSH، قم بعمل `composer install --no-dev --optimize-autoloader` على جهازك ثم ارفع مجلد `vendor` كاملاً (سيستغرق وقتاً طويلاً في الرفع).

### الخطوة 3: ربط قاعدة البيانات
1. داخل `laravel_core`، أعد تسمية `.env.example` إلى `.env`.
2. عدل البيانات بداخله لتطابق ما أنشأته في الخطوة 1:
   ```ini
   APP_URL=https://your-domain.com
   DB_DATABASE=u123_recom99
   DB_USERNAME=u123_admin
   DB_PASSWORD=your_password
   CACHE_STORE=file
   CACHE_DRIVER=file
   SESSION_DRIVER=file
   LETTER_EXPORT_DRIVER=browserless
   BROWSERLESS_BASE_URL=https://production-sfo.browserless.io
   BROWSERLESS_TOKEN=your_browserless_token
   ```
   - على استضافة Hostinger المشتركة لا يوجد Chrome/Chromium على السيرفر، لذلك يجب استخدام Browserless لتصدير خطابات PDF من لوحة الإدارة.
   - إذا ظهر خطأ `database.sqlite does not exist` فهذا يعني أن Laravel يقرأ إعدادات قديمة أو ناقصة. احذف ملفات الكاش يدوياً:
     ```bash
     rm -f bootstrap/cache/*.php
     php artisan optimize:clear
     ```
3. **تشغيل المايجريشن (بناء الجداول):**
   - عبر SSH: `php artisan migrate`
   - *بديل:* إذا لم يتوفر SSH، يمكنك استيراد ملف SQL يدوياً عبر phpMyAdmin (يمكنني تزويدك به إذا أردت).

### الخطوة 4: النشر العام (Public Access)
الآن نريد أن يرى العالم موقعك.
1. انسخ محتويات مجلد `laravel_core/public` (ملف `index.php` وغيره) وضعه في مجلد `public_html` الرئيسي.
2. افتح ملف `index.php` الموجود الآن في `public_html` وعدل المسارات:
   - ابحث عن السطر: `require __DIR__.'/../vendor/autoload.php';`
   - غيره ليشير للمكان الصحيح: `require __DIR__.'/../laravel_core/vendor/autoload.php';`
   - وكذلك السطر: `require_once __DIR__.'/../bootstrap/app.php';`
   - غيره لـ: `require_once __DIR__.'/../laravel_core/bootstrap/app.php';`

### الخطوة 5: رفع الفرونت اند (React)
1. على جهازك، قم ببناء النسخة النهائية:
   ```bash
   cd client
   npm run build
   ```
2. سيظهر مجلد `dist`. ارفع **محتوياته** (وليس المجلد نفسه) إلى `public_html`.
3. **تنبيه:** تأكد أنك لا تحذف ملف `index.php` الخاص بـ Laravel، بل ارفع ملفات React بجانبه. ستحتاج تعديل `index.html` الخاص بـ React لكي لا يتعارض، أو الأفضل:
   - اجعل React هو الواجهة الرئيسية، واجعل الـ API على رابط فرعي `/api`.
   - هذا يتطلب إعدادات `.htaccess` متقدمة قليلاً لدمج الاثنين.

**الحل الأبسط للمبتدئين (فصل المجلدات):**
1. ارفع ملفات React (محتويات dist) إلى `public_html`.
2. اترك ملفات Laravel في مجلد فرعي داخل `public_html` اسمه `api`.
3. عدل `api.js` في مشروع React ليشير لـ `https://your-domain.com/api`.
