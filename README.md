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

*تم التطوير والتجهيز بواسطة فريق العمل - موديول الـ Backend.*
