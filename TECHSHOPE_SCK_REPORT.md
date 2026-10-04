# 📑 التقرير الشامل لتحديثات نظام TechShope-SCK
**تاريخ التقرير:** 2026-10-04  
**الفرع (Git Branch):** `feature/camera-scanner`  
**حالة الحاويات (Docker):** تعمل بنجاح (12/12)

---

## 1. نظرة عامة على التحديثات (Executive Summary)

تم إنجاز تحديثين رئيسيين للنظام بنجاح تام وبشكل منظم:
1. **إضافة ميزة "ماسح الباركود بالكاميرا" (Camera Barcode Scanner):**
   - دعم المسح المباشر عبر كاميرا الحاسوب (PC / Webcam).
   - دعم المسح اللاسلكي عبر كاميرا الهاتف الذكي باستخدام جسر فوري مشفّر بتقنية **Server-Sent Events (SSE)** دون الحاجة لتثبيت أي تطبيق على الهاتف.
   - الحفاظ التام على أداء قارئ الباركود الفيزيائي التقليدي (USB Barcode Scanner) دون أي تعارض أو إبطاء.
2. **العزل التام لبيئة Docker (Full Container & Environment Isolation):**
   - إعادة تسمية جميع الحاويات والشبكات ووحدات التخزين لتكون خاصة بنسخة **`techshope-sck`**.
   - تغيير منافذ الاتصال وقاعدة البيانات لتعمل بمحاذاة النسخة القديمة (Shopping) دون أي تداخل في المنافذ أو البيانات.

---

## 2. ميزة ماسح الباركود بالكاميرا (Camera Scanner Feature)

### أ. البنية التقنية (Architecture)
يعمل النظام من خلال تدفق مزدوج:

```
┌────────────────────────────────────────────────────────┐
│                   نقطة البيع (POS Screen)              │
│                 http://localhost:18081/pos             │
└──────────┬─────────────────────────────────▲───────────┘
           │ (1) فتح النافذة                 │ (4) إدخال المنتج تلقائياً
           ▼                                 │     إلى سلة المشتريات
   توليد Token فريد                           │
   + عرض QR Code                             │
           │                                 │
           │ (2) مسح الـ QR                  │ (3) استماع SSE فوري
           ▼                                 │     EventSource Stream
┌─────────────────────────┐                  │
│      هاتف الكاشير       ├──────────────────┘
│  /pos/scanner/mobile    │  Push Barcode عبر HTTP POST
│  فتح الكاميرا + كشاف    │
└─────────────────────────┘
```

### ب. الملفات المنشأة والمعدّلة

| الملف | نوع الإجراء | الوظيفة |
| :--- | :--- | :--- |
| `app/Http/Controllers/CameraScannerController.php` | **جديد** | التحكم في توليد الرموز السرية (Tokens)، بث الـ SSE، استقبال الباركود من الهاتف، والبحث الفوري عن المنتجات في المخزون. |
| `resources/views/pos/scanner-mobile.blade.php` | **جديد** | واجهة ويب خفيفة للهواتف الذكية: كاميرا بدقة عالية، زر تشغيل الفلاش (Torch)، اهتزاز وتنبيه صوتي (Beep) عند القراءة. |
| `resources/views/pos/index.blade.php` | **تعديل** | إضافة زر الكاميرا الأزرق (📷)، النافذة المنبثقة (Modal) بتبويبين (حاسوب / هاتف)، وربط الـ SSE بسلة المشتريات. |
| `routes/web.php` | **تعديل** | تسجيل مسارات الماسح (`/pos/scanner/*`). |
| `bootstrap/app.php` | **تعديل** | استثناء مسار الدفع (`/pos/scanner/push`) من حماية CSRF لتمكين الهواتف الخارجية من الإرسال عبر الـ Token. |

### ج. المسارات الجديدة (Routes)
- `GET /pos/scanner/token` — إنشاء جلسة مسح مؤقتة مرتبطة بالمستخدم (TTL: 30 دقيقة).
- `GET /pos/scanner/stream?token=...` — قناة بث SSE مباشرة تربط شاشة الـ POS بالخادم.
- `POST /pos/scanner/push` — استقبال الباركود من الهاتف مع حماية Rate Limiting (60 طلب/دقيقة).
- `GET /pos/scanner/lookup?barcode=...` — جلب بيانات المنتج بالباركود.
- `GET /pos/scanner/mobile?token=...` — شاشة الماسح للهاتف الذكي.

---

## 3. العزل الشامل لبيئة Docker (Isolated Environment)

لضمان عدم حدوث أي تضارب مع مشروع التسوق القديم في حال كان يعمل على نفس الجهاز:

### أ. جدول مقارنة الموارد المعزولة

| المورد | النسخة السابقة (Shopping) | النسخة الجديدة (`techshope-sck`) |
| :--- | :--- | :--- |
| **حاوية التطبيق (App)** | `techshope_app_v2` | **`techshope_sck_app`** |
| **حاوية الويب (Nginx)** | `techshope_web_v2` | **`techshope_sck_web`** |
| **منفذ الويب (HTTP)** | `18080` | **`18081`** |
| **حاوية قاعدة البيانات** | `techshope_pgsql_v2` | **`techshope_sck_pgsql`** |
| **منفذ قاعدة البيانات الخارجي** | `15432` / `55432` | **`15433`** |
| **اسم قاعدة البيانات** | `droguerie_s` | **`techshope_sck`** |
| **حاوية طابور المهام (Queue)** | `techshope_queue_v2` | **`techshope_sck_queue`** |
| **حاوية Vite Dev Server** | `techshope_vite_v2` (15173) | **`techshope_sck_vite` (15174)** |
| **الشبكة المعزولة** | `techshope_net_v2` | **`techshope_sck_net`** |
| **بيانات قاعدة البيانات** | `techshope_pgdata_v2` | **`techshope_sck_pgdata`** |
| **مكتبات PHP (Vendor)** | `techshope_vendor_v2` | **`techshope_sck_vendor`** |
| **مجلدات الذاكرة المؤقتة** | `techshope_bootstrap_cache_v2` | **`techshope_sck_bootstrap_cache`** |
| **مجلدات التخزين** | `techshope_storage_framework_v2` | **`techshope_sck_storage_framework`** |

### ب. ملفات الإعداد المعدلة
- `docker-compose.yml`: إعادة تسمية جميع الخدمات والمنافذ ووحدات التخزين.
- `.env`: تحديث اسم التطبيق ومنفذ الاتصال وبيانات الـ Database.
- `.env.docker.example`: تحديث النموذج الافتراضي.
- `docker/nginx/default.conf`: ربط الـ FastCGI مع `techshope_sck_app:9000`.
- `docker/php/Dockerfile`: ربط الإعدادات بـ `99-techshope-sck.ini` و `techshope-sck-entrypoint`.

---

## 4. سجل الـ Git Commits

تم حفظ كل مرحلة في commit مستقل على فرع `feature/camera-scanner`:
1. `commit 213bc76`:  
   `feat(scanner): add camera-based barcode scanner (Phase 2)`  
   (إضافة كافة ملفات ووظائف الماسح الضوئي للكاميرا والهاتف والـ POS).
2. `commit 1fb3d0c`:  
   `chore(docker): isolate container names, ports, volumes, and database for techshope-sck`  
   (تحديث وعزل إعدادات Docker بالكامل وتغيير المنافذ وقاعدة البيانات).

---

## 5. دليل التشغيل والاستخدام السريع

### أ. روابط الوصول
- **رابط التطبيق الرئيسي:** [http://localhost:18081](http://localhost:18081)
- **شاشة نقطة البيع (POS):** [http://localhost:18081/pos](http://localhost:18081/pos)

### ب. طريقة تشغيل الحاويات مستقبلاً
```bash
# تشغيل الحاويات في الخلفية
docker compose up -d

# متابعة السجلات (Logs)
docker compose logs -f app

# إيقاف الحاويات
docker compose down
```

### ج. تجربة الماسح الضوئي
1. سجّل الدخول إلى النظام ثم انتقل إلى شاشة **POS**.
2. بجانب حقل البحث عن الباركود، اضغط على **زر الكاميرا الأزرق (📷)**.
3. ستظهر نافذة تحتوي على خيارين:
   - **كاميرا الحاسوب (PC Webcam):** اضغط "بدء الكاميرا" لتوجيه الباركود نحو الكاميرا وإضافته فوراً للسلة.
   - **كاميرا الهاتف (Mobile Sync):** امسح رمز الـ QR بالهاتف الذكي أو افتح الرابط المعروض، ثم ابدأ بمسح المنتجات من الهاتف لتظهر مباشرة في شاشة الحاسوب.
