# Distributed Real-Time E-Commerce Platform 🛒

منصة تجارة إلكترونية لبناء متجر متكامل مع مراعاة الأمان والأداء وفصل الموديولات (Modular Monolith) بأسلوب يسهل التوسع والتحول الموزع لاحقاً.

---

## 📌 أسلوب النظام والإصدارات (Versioning & Structure)

* **المعيار المتبع:** تم بناء الـ API كاملاً تحت الإصدار الأول `/api/v1/`.
* **توحيد الردود (API Envelope):** جميع الردود ترجع بنفس الهيكلية (سواء نجاح أو خطأ) لتسهيل الربط مع الفرونت إند.
* **النمط المعماري (Modular Monolith):** الكود مقسم داخل مجلد `app/Modules/` إلى 4 وحدات مستقلة تماماً، وممنوع أي موديول يستدعي جداول موديول آخر بشكل مباشر.

---

## 📂 أين توجد الملفات والـ Routes؟

تتوزع الملفات والمسارات داخل المشروع بالشكل التالي:

```text
app/Modules/
├── Users/
│   ├── Controllers/          → معالجة التسجيل والدخول والبروفايل
│   ├── Models/               → User Model
│   ├── Routes/api.php        → مسارات Auth (/api/v1/auth/...)
│   └── Database/Migrations/  → جدول المستخدمين والـ Roles
│
├── Products/
│   ├── Controllers/          → عرض وإضافة المنتجات والتصنيفات
│   ├── Models/               → Product & Category Models
│   ├── Routes/api.php        → مسارات المنتجات (/api/v1/products/...)
│   └── Database/Migrations/  → جداول المنتجات والتصنيفات
│
├── Orders/
│   ├── Controllers/          → إنشاء الطلبات وتغيير الحالات
│   ├── Models/               → Order & OrderItem Models
│   ├── Routes/api.php        → مسارات الطلبات (/api/v1/orders/...)
│   └── Database/Migrations/  → جدول الطلبات وتفاصيلها
│
└── Notifications/
    ├── Services/             → معالجة الإشعارات عبر الـ Queue
    ├── Routes/api.php        → مسارات الإشعارات (/api/v1/notifications/...)
    └── Database/Migrations/  → جدول الإشعارات

```

---

## 🔗 مسارات الـ API المتاحة (Routes Map)

جميع المسارات تبدأ بـ `/api/v1`:

| الخدمة | Route | Method | الصلاحية (Access) |
| --- | --- | --- | --- |
| **المصادقة** | `/auth/register` | `POST` | للجميع |
|  | `/auth/login` | `POST` | للجميع (محددة بـ 5 محاولات/دقيقة) |
|  | `/auth/me` | `GET` | للمسجلين فقط |
| **المنتجات** | `/products` | `GET` | للجميع (مع Pagination وتصفية) |
|  | `/products/{id}` | `GET` | للجميع |
|  | `/products` | `POST` | الأدمن فقط |
|  | `/products/categories` | `GET` | للجميع |
| **الطلبات** | `/orders` | `POST` | زبون مسجل |
|  | `/orders` & `/orders/{id}` | `GET` | صاحب الطلب فقط |
|  | `/orders/{id}/status` | `PATCH` | الأدمن فقط |
| **الإشعارات** | `/notifications` | `GET` | للمستخدم صاحب الإشعار |

---

## 🚀 أوامر التشغيل (How to Run)

شغل **Docker Desktop** أولاً، ثم نفذ الأوامر التالية بالترتيب في الـ Terminal:

```bash
# 1. نسخ ملف البيئة
cp .env.example .env

# 2. تشغيل حاويات Docker
docker compose up -d --build

# 3. تثبيت مكتبات Composer
docker compose exec app composer install

# 4. توليد مفاتيح التشفير والـ JWT
docker compose exec app php artisan key:generate
docker compose exec app php artisan jwt:secret --forced

# 5. إنشاء الجداول وتغذيتها بالبيانات التجريبية
docker compose exec app php artisan migrate --seed

```

---

## 🧪 اختبار التشغيل والحسابات

* **رابط الـ API:** `http://localhost:8000/api/v1/products`
* **صندوق البريد التجريبي (Mailpit):** `http://localhost:8025`
* **حسابات التجربة الجاهزة (Password: `password123`):**
* الأدمن: `admin@store.test`
* المستخدمين: `alice@test.com` | `bob@test.com`



---

## 🌿 قواعد العمل على Git

1. **فرع `main` محمي:** لا يُسمح بالرفع المباشر عليه.
2. **العمل عبر الفروع:** كل مطور يعمل على فرعه الخاص .
3. **الدمج (Pull Request):** يتم طلب Pull Request إلى فرع .

---
