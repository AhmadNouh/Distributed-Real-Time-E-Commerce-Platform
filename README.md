# Distributed Real-Time E-Commerce Platform 🚀

منصة تجارة إلكترونية موزعة ومدعومة بمعالجة الأحداث غير المتزامنة والتحديثات اللحظية (Event-Driven & Real-Time Architecture)، تم بناؤها باستخدام **Laravel 11** وتأطيرها بالكامل عبر **Docker**.

---

## 📐 نظرة عامة على المعمارية (Architecture Overview)

تعتمد المنصة على معمارية معالجة الأحداث (Event-Driven Architecture) وفصل المهام الاستجابية عن مهام الخلفية لضمان أداء عالٍ واستجابة سريعة للمستخدم:

1. **مستقبل الطلبات (HTTP API Layer):** يُستقبل طلب العميل عبر Laravel، ويتم حفظ الطلب في قاعدة البيانات فوراً مع إطلاق الأحداث (Events).
2. **طابور الرسائل (Message Queue - RabbitMQ):** يتم تمرير أحداث خلفية مثل إرسال البريد الإلكتروني والبث اللحظي عبر RabbitMQ لضمان عدم تأخير استجابة الـ HTTP API.
3. **عمال الخلفية (Queue Workers):** تقوم حاوية مستقلة (`queue-worker`) بمعالجة المهام غير المتزامنة من RabbitMQ بشكل منفصل.
4. **البث اللحظي (Real-Time Broadcasting - Pusher):** يتم إرسال التحديثات الحية فور حدوثها إلى لوحة تحكم المسؤول وسجل العميل عبر WebSockets/Pusher.
5. **معاينة البريد الإلكتروني (Mailpit Server):** تُوجّه كافة الرسائل الصادرة محلّياً إلى سيرفر Mailpit لمُعاينة الإشعارات فورياً عبر واجهة ويب دون الحاجة لإرسال إيميلات حقيقية.

---

## 🛠️ المكونات والتقنيات المستخدمة (Tech Stack)

* **Backend Framework:** Laravel 11 (PHP 8.3)
* **Containerization:** Docker & Docker Compose
* **Web Server:** Nginx
* **Database:** PostgreSQL
* **Message Broker:** RabbitMQ
* **Real-Time Engine:** Pusher Channels / Laravel Broadcasting
* **Mail Driver & Testing:** Mailpit (SMTP Server & Web Dashboard) / Laravel Mail (Queued via RabbitMQ)

---

## 🏗️ هيكلية الخدمات داخل Docker (Docker Services)

تتكون الحاويات المدارية عبر `docker-compose.yml` من:

* `app`: حاوية التطبيق الرئيسية (Laravel PHP-FPM).
* `web`: خادم Nginx لاستقبال ومعالجة طلبات الـ HTTP.
* `postgres`: قاعدة البيانات الرئيسية للنظام.
* `rabbitmq`: خادم إدارة طوابير الرسائل والأحداث مع لوحة التحكم (Management Plugin).
* `queue-worker`: حاوية مخصصة لتشغيل `php artisan queue:work` لتنفيذ المهام خلف الكواليس.
* `mailpit`: خادم استقبال ومعاينة البريد الإلكتروني محلياً عبر واجهة الويب.

---

## ⚙️ التجهيز والميزات المكتملة حتى الآن

### 1. معالجة طلبات الشراء غير المتزامنة (Order Creation)
* عند إنشاء طلب جديد عبر `POST /orders`، يتم حفظ الطلب وإطلاق الحدث `OrderCreatedRealTime`.
* يُبث الحدث عبر القناة العامة `admin-orders` باسم الحدث `order.created` لإخطار الإدارة فوراً بدون إنعاش الصفحة.

### 2. تحديث حالة الطلب وإشعارات البريد (Order Status Updates & Mail)
* عند تعديل حالة الطلب بواسطة المسؤول عبر `PATCH /orders/{id}/status`، يتم إطلاق الحدث `OrderStatusUpdated`.
* يتم بث الحدث لحظياً للعميل عبر القناة المخصصة `orders.{order_id}` باسم الحدث `order.status.updated`.
* يتم إرسال بريد إلكتروني غير متزامن للمستخدم عبر `SendOrderStatusUpdatedNotification` وقالب البريد `OrderStatusUpdatedMail` دون تعطيل أو تأخير الـ API، مع إمكانية معاينة الإيميل مباشرة في **Mailpit**.

---

## 🚀 خطوات التشغيل والتهيئة المحلية (Local Setup Guide)

### 1️⃣ متطلبات التشغيل المبسطة
* تثبيت **Docker Desktop** على جهازك.
* تثبيت **Git**.

### 2️⃣ استنساخ المشروع وإعداد متغيرات البيئة `.env`

1. قم باستنساخ المستودع (Repository):
   ```bash
   git clone <REPOSITORY_URL>
   cd Distributed-Real-Time-E-Commerce-Platform
   ```

2. أنشئ ملف البيئة `.env` من النسخة الافتراضية:
   ```bash
   cp .env.example .env
   ```

3. قم بتعبئة الإعدادات بداخل `.env` (Pusher، RabbitMQ، PostgreSQL، و Mailpit):
   ```env
   # البث اللحظي
   BROADCAST_CONNECTION=pusher
   PUSHER_APP_ID="YOUR_PUSHER_APP_ID"
   PUSHER_APP_KEY="YOUR_PUSHER_APP_KEY"
   PUSHER_APP_SECRET="YOUR_PUSHER_APP_SECRET"
   PUSHER_APP_CLUSTER="YOUR_PUSHER_APP_CLUSTER"

   # طابور الرسائل
   QUEUE_CONNECTION=rabbitmq
   RABBITMQ_HOST=rabbitmq
   RABBITMQ_PORT=5672
   RABBITMQ_USER=guest
   RABBITMQ_PASSWORD=guest
   RABBITMQ_QUEUE=orders_queue

   # إعدادات البريد الإلكتروني (Mailpit)
   MAIL_MAILER=smtp
   MAIL_HOST=mailpit
   MAIL_PORT=1025
   MAIL_USERNAME=null
   MAIL_PASSWORD=null
   MAIL_ENCRYPTION=null
   MAIL_FROM_ADDRESS="noreply@ecommerce.local"
   MAIL_FROM_NAME="${APP_NAME}"
   ```

---

### 3️⃣ إعداد `docker-compose.yml` (خدمة Mailpit وتمرير البيئة)

تأكد من إضافة خدمة `mailpit` وتمرير متغيرات البريد والبث إلى حاويتي `app` و `queue-worker`:

```yaml
version: '3.8'

services:
  # خادم معاينة البريد الإلكتروني
  mailpit:
    image: 'axllent/mailpit:latest'
    container_name: mailpit
    restart: unless-stopped
    ports:
      - '8025:8025' # واجهة الويب (Web UI)
      - '1025:1025' # منفذ SMTP
    networks:
      - ecommerce-network

  # حاوية التطبيق الرئيسية
  app:
    environment:
      BROADCAST_CONNECTION: pusher
      PUSHER_APP_ID: "${PUSHER_APP_ID}"
      PUSHER_APP_KEY: "${PUSHER_APP_KEY}"
      PUSHER_APP_SECRET: "${PUSHER_APP_SECRET}"
      PUSHER_APP_CLUSTER: "${PUSHER_APP_CLUSTER}"
      MAIL_MAILER: smtp
      MAIL_HOST: mailpit
      MAIL_PORT: 1025
      MAIL_FROM_ADDRESS: "${MAIL_FROM_ADDRESS}"
      MAIL_FROM_NAME: "${MAIL_FROM_NAME}"

  # حاوية الـ Queue Worker
  queue-worker:
    environment:
      BROADCAST_CONNECTION: pusher
      PUSHER_APP_ID: "${PUSHER_APP_ID}"
      PUSHER_APP_KEY: "${PUSHER_APP_KEY}"
      PUSHER_APP_SECRET: "${PUSHER_APP_SECRET}"
      PUSHER_APP_CLUSTER: "${PUSHER_APP_CLUSTER}"
      MAIL_MAILER: smtp
      MAIL_HOST: mailpit
      MAIL_PORT: 1025
      MAIL_FROM_ADDRESS: "${MAIL_FROM_ADDRESS}"
      MAIL_FROM_NAME: "${MAIL_FROM_NAME}"
```

---

### 4️⃣ تشغيل الحاويات وتطبيق التهجيرات

1. تشغيل بيئة Docker وإعادة بناء الحاويات بالقيم الجديدة:
   ```bash
   docker compose up -d --build
   ```

2. تثبيت الحزم وتوليد المفتاح الرئيسي للمشروع:
   ```bash
   docker compose exec app composer install --ignore-platform-reqs
   docker compose exec app php artisan key:generate
   ```

3. تشغيل تهجيرات قاعدة البيانات (Migrations):
   ```bash
   docker compose exec app php artisan migrate
   ```

4. مسح الكاش وتأكيد تشغيل العامل:
   ```bash
   docker compose exec app php artisan config:clear
   docker compose restart queue-worker
   ```

---

## 🧪 كيفية التحقق والاختبار (Testing & Live Debugging)

### 1. اختبار معاينة البريد الإلكتروني عبر Mailpit 📬
* افتح واجهة **Mailpit** في المتصفح عبر الرابط: **`http://localhost:8025`**
* عند تعديل حالة الطلب عبر Postman (`PATCH /orders/{id}/status`)، سيقوم الـ Worker بمعالجة المهمة فوراً.
* ستشاهد الإيميل المنسق قد وصل فوراً في شاشة Mailpit متضمناً رقم الطلب والحالة الجديدة.

### 2. اختبار البث اللحظي (Real-Time Test)
* افتح لوحة التحكم في **Pusher Debug Console** الخاصة بتطبيقك.
* أرسل طلب إنشاء طلب `POST /orders` أو تعديل حالة طلب `PATCH /orders/{id}/status` عبر **Postman**.
* ستلاحظ وصول الأحداث فوراً بشريط أخضر متضمناً تفاصيل الطلب (`Payload`).

### 3. اختبار الواجهة البسيطة (Frontend HTML Demo)
* يتوفر ملف اختبار بسيط `realtime_dashboard.html` يستمع لقناة `admin-orders` وقناة `orders.{id}`.
* افتح الملف في أي متصفح، ثم أرسل الطلب عبر Postman لمشاهدة البطاقات التنبيهية تظهر لحظياً في الصفحة.

### 4. متابعة سجلات العامل (Queue Worker Logs)
يمكنك متابعة معالجة المهام غير المتزامنة لحظة بلحظة عبر:
```bash
docker compose logs -f queue-worker
```

---

## 🔗 روابط اللوحات المحلية (Local Dashboards & Tools)

* **Mailpit Web UI (معاينة الإيميلات):** `http://localhost:8025`
* **RabbitMQ Management Dashboard:** `http://localhost:15672` (اسم المستخدم: `guest` / كلمة السر: `guest`)

---

## 🔒 ملاحظات هامة للأمان ورفعه على GitHub

* **ملف `.env`:** محظور تماماً من الرفع على GitHub لاحتوائه على مفاتيح Pusher وكلمات سر قواعد البيانات. تأكد من وجوده بداخل `.gitignore`.
