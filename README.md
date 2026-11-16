<<<<<<< HEAD
# E-Commerce Modular Monolith (Laravel 12)

منصة تجارة إلكترونية مبنية بهيكلية **Modular Monolith** باستخدام **Laravel 12** و **PostgreSQL**، مع نظام مصادقة تعتمد على الـ JWT (`tymon/jwt-auth`) وإدارة الطلبات والإشعارات.

---

## 📂 هيكلية المشروع (Project Structure)

المشروع مقسم إلى موديولات مستقلة (Modules) لتسهيل الصيانة والتطوير:

* **Users**: إدارة المستخدمين، الأدوار (`admin`, `customer`)، والمصادقة.
* **Products**: إدارة المنتجات، التصنيفات (`Categories`)، والأسعار.
* **Orders**: إدارة الطلبات، تفاصيل المنتجات داخل الطلب، وتتبع حالات الطلب.
* **Notifications**: إدارة الإشعارات وتحديدها كمقروءة عبر قاعدة البيانات (Database Notifications).

---

## 🛠️ التعديلات والتحسينات الأخيرة

1. **نظام المصادقة (Auth & JWT):** الاعتماد على `tymon/jwt-auth` والـ `api` guard مع التحقق السليم من الـ Bearer Token.
2. **التحكم بالصلاحيات (RBAC):** تحديث التحقق من صلاحيات الأدمن بالاعتماد على حقل `role === 'admin'`.
3. **معالجة الاستثناءات (Error Handling):** جعل كافة مسارات الـ API تعيد استجابات `JSON` نظيفة (مثل رموز `404`, `403`, `422`) بدلاً من انهيار التطبيق أو إرجاع صفحات HTML.
4. **إدارة الإشعارات والطلبات:** تحسين دوال البحث عبر الـ `id` والـ `uuid` لضمان عدم حدوث أخطاء `null pointer` أو `NotFoundHttpException`.
5. **إضافة التصنيفات (Categories Seeder):** دمج `CategoryDatabaseSeeder` لتعبئة التصنيفات الأساسية للمنتجات.

---

## 🌱 إعداد وتعبئة قاعدة البيانات (Seeders)

يحتوي كل موديول على الـ Seeder الخاص به، ويتم استدعاؤها جميعاً من الملف الرئيسي `DatabaseSeeder.php` بالترتيب الآتي:

1. `UserDatabaseSeeder` (إنشاء مستخدمي الأدمن والزبائن)
2. `CategoryDatabaseSeeder` (إنشاء تصنيفات المنتجات)
3. `ProductDatabaseSeeder` (إضافة المنتجات الأولية وربطها بالتصنيفات)
4. `OrderDatabaseSeeder` (إنشاء طلبات تجريبية)
5. `NotificationDatabaseSeeder` (إضافة إشعارات تجريبية)

---

## 🚀 أوامر التشغيل والتثبيت (Docker & Artisan)

### 1. تشغيل الحاويات (Docker)

```bash
docker compose up -d

```

### 2. تثبيت الحزم (Composer Install)

```bash
docker compose exec app composer install

```

### 3. إعداد مفتاح التوكن (JWT Secret)

```bash
docker compose exec app php artisan jwt:secret

```

### 4. تشغيل الهجرة وتعبئة البيانات (Migrations & Seeders)

```bash
docker compose exec app php artisan migrate:fresh --seed

```

### 5. مسح الكاش (Cache Clear)

```bash
docker compose exec app php artisan optimize:clear

```

---

## 🔀 أوامر Git الخاصة بالفرع (`backend-ahmad`)

إذا واجهتك مشاكل في التزامن مع المستودع على جيت هب، استخدم الأوامر التالية:

```bash
# سحب التعديلات الحديثة ودمجها مع الفرع الحالي
git pull origin backend-ahmad --rebase

# رفع التعديلات النهائية على الفرع
git push -u origin backend-ahmad

```

---

## 🏭 المتطلب الثاني: التواصل بين الخدمات (Warehouse gRPC Service)

### الفكرة

المخزون (stock) لم يعد داخل قاعدة بيانات Laravel. أصبح هناك **خدمة مستقلة بلغة Go** تتكلم **gRPC** وتملك **قاعدة بيانات PostgreSQL خاصة بها**:

```
Laravel (app)  ──gRPC 2s deadline──▶  warehouse (Go, :50051)  ──▶  warehouse-db (PostgreSQL, host :5433)
   │
   └── orders / products  →  ecommerce (PostgreSQL, host :5432)
```

* **العقد الوحيد بين الطرفين:** `proto/warehouse/v1/warehouse.proto` ويحتوي **٣ عمليات فقط**:
  1. `GetStock(product_id)` → الكمية المتاحة.
  2. `Reserve(order_uuid, items[])` → حجز كل عناصر الطلب **بنداء واحد** (إما الكل أو لا شيء).
  3. `Release(order_uuid)` → إرجاع الحجز (للتعويض أو عند الإلغاء).
* Laravel **لا يقرأ** جداول المخزون مباشرة أبداً.

### المجلدات والأماكن

| ماذا | أين |
|---|---|
| العقد (proto) | `proto/warehouse/v1/warehouse.proto` |
| خدمة Go | `services/warehouse/cmd/server/main.go` |
| منطق المخزون (الأقفال + idempotency) | `services/warehouse/internal/warehouse/store.go` |
| طبقة gRPC | `services/warehouse/internal/warehouse/server.go` |
| سكيما قاعدة المستودع + seed | `services/warehouse/internal/warehouse/schema.sql` |
| منفذ المخزون في Laravel | `app/Shared/Contracts/InventoryClient.php` |
| تطبيق gRPC في Laravel | `app/Shared/Inventory/GrpcInventoryClient.php` |
| تطبيق fake للاختبارات | `app/Shared/Inventory/FakeInventoryClient.php` |
| ربط التنفيذ بالإعدادات | `app/Providers/InventoryServiceProvider.php` |
| استثناءات المخزون | `app/Shared/Exceptions/` |
| منطق إنشاء الطلب | `app/Modules/Orders/Services/OrderServiceImpl.php` |
| عرض الكمية في المنتج | `app/Modules/Products/Http/Controllers/ProductController.php` |
| اختبارات فشل المخزون | `app/Modules/Orders/Tests/Feature/OrderInventoryTest.php` |
| اختبارات عرض الكمية | `app/Modules/Products/Tests/ProductStockTest.php` |
| اختبارات وحدة (ReservationResult / Fake / gRPC mapping) | `tests/Unit/` |
| اختبار وحدة لمنطق المستودع في Go | `services/warehouse/internal/warehouse/store_test.go` |
| اختبار تكامل gRPC حقيقي | `tests/Integration/WarehouseGrpcTest.php` |
| كولكشن Postman (اختبارات على كل طلب) | `docs/postman/Distributed-Real-Time-E-Commerce-Platform-v2.postman_collection.json` |
| إثبات الـ race | `scripts/proof/race-condition.ps1` |
| إثبات سقوط الخدمة | `scripts/proof/warehouse-down.ps1` |
| توليد stubs الـ PHP | `docker/php/gen-stubs.sh` |
| تثبيت إضافة grpc | `docker/php/install-extensions.sh` |

### التشغيل

```bash
# 1. بناء وتشغيل كل شيء (بما فيها warehouse + warehouse-db)
docker compose up -d --build

# 2. تأكد أن خدمة المستودع سليمة (gRPC Health + reflection مفعّلان)
docker compose ps warehouse
docker compose logs -f warehouse

# 3. اختبارات المشروع (تستخدم fake ولا تحتاج الخدمة)
docker compose exec -T app php artisan test

# 4. اختبارات التكامل الحقيقية ضد خدمة Go
docker compose exec -T -e RUN_GRPC_TESTS=1 app php artisan test --testsuite=Integration
```

### متغيّرات البيئة

| المتغيّر | القيمة الافتراضية | المعنى |
|---|---|---|
| `INVENTORY_DRIVER` | `fake` | `grpc` لتشغيل الخدمة الحقيقية |
| `WAREHOUSE_ADDR` | `warehouse:50051` | عنوان خدمة Go |
| `WAREHOUSE_TIMEOUT` | `2` | مهلة كل RPC بالثواني (Deadline) |

### كيف يعمل إنشاء الطلب (خطوة بخطوة)

1. `OrderController::store()` يتحقق من صحة الطلب (`items[]`, `quantity >= 1`).
2. `OrderServiceImpl::createFor()` يجيب المنتجات من موديول Products (قراءة فقط).
3. يبني قائمة الحجز ويستدعي **`reserve($orderUuid, $items)` مرة واحدة** لكل الطلب.
4. إذا رفض المستودع:
   * نقص مخزون → استثناء `InsufficientStockException` → **HTTP 409** مع أسماء المنتجات والكميات.
   * منتج غير معروف للمستودع → نفس 409 مع رسالة مختلفة.
   * الخدمة واقفة/انتهت المهلة → `WarehouseUnavailableException` / `WarehouseTimeoutException` → **HTTP 503** بسرعة (بدون انتظار لا نهائي).
5. إذا وافق المستودع: تُكتب الطلبات داخل `DB::transaction`.
6. إذا فشل الحفظ بعد الموافقة: `releaseQuietly($orderUuid)` يرجّع الكميات للمستودع (تعويض)، ويُسجَّل الخطأ في اللوج.
7. عند تغيير حالة الطلب إلى `cancelled`: تُحرَّر الكميات تلقائياً.

### لماذا لا يمكن بيع آخر قطعة مرتين؟

* `Reserve` يفتح معاملة، ويأخذ `pg_advisory_xact_lock(hashtext(order_uuid))` لمنع تكرار نفس الطلب (idempotency).
* ثم `SELECT ... FOR UPDATE` مع `ORDER BY product_id` لقفل صفوف المنتجات بترتيب ثابت (يمنع الـ deadlocks).
* بعد انتظار القفل، PostgreSQL يعيد قراءة أحدث نسخة من الصف، فيرى `reserved` الذي زاده الطلب الفائز.
* أي طلب آخر يجد `available < quantity` فيُرجع `INSUFFICIENT_STOCK` بدون أي كتابة.
* يوجد `CHECK (reserved <= quantity)` كحزام أمان إضافي على مستوى قاعدة البيانات.

### إثبات الـ Race Condition

```powershell
powershell -ExecutionPolicy Bypass -File scripts/proof/race-condition.ps1
```

الناتج المتوقع:

```
OK  warehouse reports exactly 1 available unit before the race
OK  exactly ONE order was created
OK  the other 9 requests were rejected with 409
OK  no unit is left - the last one was sold exactly once
RACE CONDITION PROOF PASSED: 10 concurrent buyers, 1 unit, 1 success.
```

### إثبات سقوط خدمة المستودع

```powershell
powershell -ExecutionPolicy Bypass -File scripts/proof/warehouse-down.ps1
```

* صفحة المنتج تبقى تعمل: `available_quantity = null` و`stock_status = "unavailable"`.
* إنشاء الطلب يرجع **503** خلال ~٢ ثانية مع رسالة واضحة.
* السكربت يعيد تشغيل الخدمة ويتأكد من رجوع الصفحة لحالتها الطبيعية.

### اختبارات Postman (كل طلب عنده Tests)

الملف: `docs/postman/Distributed-Real-Time-E-Commerce-Platform-v2.postman_collection.json`
(نسخة مطابقة له في جذر المشروع: `Distributed-Real-Time-E-Commerce-Platform-v2.postman_collection.json`).

* ٥ مجلدات / ٢٥ طلب، **وكل طلب فيه Test Script** (Auth, Products, Orders, Notifications, Warehouse gRPC).
* تشغيل آلي كامل:

```powershell
npx -y newman run docs/postman/Distributed-Real-Time-E-Commerce-Platform-v2.postman_collection.json
```

النتيجة المتوقعة: `25 requests / 53 assertions / 0 failures` — البندان اليدويان (٤ و٥) مُتخطّيان افتراضياً حتى لا تحتاج لإيقاف الحاويات.

* **٤. Warehouse Down → 503:** `docker compose stop warehouse` ثم اجعل `run_warehouse_down = true` وأرسل الطلب (ثم `docker compose start warehouse`).
* **٥. After Race: Last Unit Is Gone:** نفّذ `scripts/proof/race-condition.ps1` ثم اجعل `run_race = true` وأرسل الطلب.

### ملاحظات تشغيلية

* أول بناء لصورة `app` ياخد وقتاً لأن إضافة `grpc` تُترجم من المصدر. سكربت `docker/php/install-extensions.sh` يحدّد `make -j2` لتجنّب نفاد الذاكرة على Docker بذاكرة ٤ GB.
* لإعادة توليد ملفات PHP من الـ proto بعد أي تعديل:

```bash
docker compose exec -T -u root app sh /var/www/html/docker/php/gen-stubs.sh
```

* لإعادة توليد ملفات Go:

```bash
docker run --rm -v "$($PWD.Path):/w" -w /w golang:1.23-bookworm \
  bash /w/services/warehouse/scripts/gen-proto.sh
```

* لفحص كل ملفات PHP نحوياً:

```bash
docker compose exec -T app sh /var/www/html/scripts/lint.sh
```

---

*تم التطوير والتجهيز بواسطة فريق العمل - موديول الـ Backend.*


=======
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
>>>>>>> 4787a5311a427fbf4aad828843323dda5b56377f
