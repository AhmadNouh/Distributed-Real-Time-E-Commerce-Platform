# Sync report: Flutter app vs latest backend vs web app (2026-11-04)

## 1. Repo state

- `git fetch --all --prune` brought in: `backend-ahmad` `c4af866..007987b`, `web-hazem` `7c99927..33b8051`, `backend` and `backend-reema` (force-updated, now `5d0b8cf` / `007987b`).
- Local `mobile-yara` was **6 commits ahead** of `origin/mobile-yara` (`03e1c60`) and not diverged. No fast-forward was needed.
- `origin/main` (`4787a53`) was already contained in `mobile-yara`: `git merge origin/main` printed "Already up to date". Nothing merged, no conflicts.
- `backend-ahmad` and `backend-reema` point to the same commit (`007987b`). `backend-ahmad` was used.
- `mobile-yara` already contained the older backend commit `c4af866` (merged earlier through `backend-ahmad`). The newer backend work (`c4af866..007987b`: contracts, warehouse, Postman) is **not** in `mobile-yara` or `main`, as instructed.
- Local `main` is 9 commits ahead of `origin/main`. This looks accidental and was not touched.

## 2. Contract (live backend `007987b`, checked with curl)

Envelope on most routes: `{"success":bool,"data":...,"message":str,"errors":null|{...}}`.
The app unwraps it in `ApiClient`.

| Endpoint | Latest backend (real response) | Web app | Flutter app (after this sync) |
|---|---|---|---|
| `POST /auth/login` | 200 `data:{user:{id,name,email,phone,created_at,updated_at}, token}`. **No `role` in `user`**; JWT payload has `role`. Token has no `token_type` or `expires_in` (JWT ttl 60 min). | Reads `token` or `access_token`, role from `role` / `roles[]` / `is_admin` / JWT | Reads `token`; calls `/auth/me` for the user. OK |
| `POST /auth/register` | `name,email,password,password_confirmation` (confirmed, min 6). 201 same shape as login. 422 `{message,errors:{field:[..]}}` (no envelope) | Sends `password_confirmation` | Sends `password_confirmation`. OK |
| `POST /auth/logout`, `GET /auth/me` | 200 envelope; `me` has `data` = user. After logout, `me` gives 401 `{"message":"Unauthenticated."}` | OK | OK |
| `GET /products` | `data` = Laravel paginator (`current_page,data[],last_page,per_page,total,...`), 15 per page, `per_page` and `page` honoured. Row: `id`(int), `name`, `description`, `price` (**string** `"75.00"`), `category_id`, `image` (null / relative path), timestamps. **No `stock`, no category name** | Page 1 only; filters on the client; reads `product.stock` (**never sent**) | Server-side filter (`category_id`, `search`), now all pages. No invented stock. OK |
| `GET /products/{id}` | Row plus `available_quantity` (int/null) and `stock_status` (`available`/`unavailable`) from the warehouse | Not used for stock | **New:** details screen reads `available_quantity`, disables Add to cart at 0 |
| `GET /products/categories` | **Not enveloped**: plain array `[{id,name,...}]` | `{id,name}` | OK (plain list handled) |
| Search / filter | `search` (LIKE on name, **case-sensitive**: `key` finds nothing, `Key` does), `category_id` (id) | none (client) | Same params |
| `POST /orders` | Body `{items:[{product_id,quantity}], uuid?}`. 201 `data` = order `{id,user_id,status:"pending",total (number),uuid,items[{product_id,quantity,price}]}`. **Items have no product name.** No `shipping_address` | Sends only `items` | Same payload. Names come from the cart |
| `GET /orders`, `/orders/{id}` | Paginator under `data`. `total` is a **string** here. Status lowercase: `pending,confirmed,shipped,delivered,cancelled`. 404 `{success:false,message:"Order not found"}` | Capitalises status. Expects `product_name`, `order_number`, `shipping_address` (**never sent**) | Capitalises status, tolerant of string/number. Now all pages, newest first, names for all items |
| Insufficient stock | **409** `message:"Insufficient stock for this order. X: requested N, available M."`, `errors:{stock:[{product_id,requested,available,name}]}` | no handling seen | **Fixed:** shows `message` (was printing a raw map) |
| Warehouse down | **503** `errors:{warehouse:[msg]}` (from README and Postman; **not reproduced live**) | none | `message` shown |
| `GET /notifications` | `data` = plain list, plus `meta:{current_page,last_page,per_page,total,unread_count}`. Item: `id` (uuid), `type`, `data:{message,order_id}`, `read_at`, `is_read`, `created_at`, `created_human`. Created asynchronously by the queue worker | `notificationsApi` is defined but **no UI uses it**. Expects `title`/`message` at the top level (wrong) | Reads `data.message`, `read_at`. Now follows `meta.last_page` |
| `PATCH /notifications/{id}/read` | 200 `data` = notification with `read_at`. Non-UUID id gives a **500 with SQL and stack trace** | same | OK |
| 401 / 403 | Unauthenticated: `{"message":"Unauthenticated."}`; admin only: `{success:false,message:"Forbidden. Admins only.",debug_role}` | | message shown; 401 clears the token |
| 422 | Laravel default `{message,errors:{field:[..]}}` | | first error shown |
| 429 (login) | Laravel throttle, 5 per minute **per IP**, `Retry-After` header, `message:"Too Many Attempts."` (plain Laravel JSON, not the `{error:{...}}` of `RateLimitLogin`, which is unused) | | OK |

**Mismatches that mattered for Flutter**
1. `stock` was invented as 20 (the API never sends it).
2. 409 body was printed as a Dart map.
3. Product names for orders came from page 1 of `/products` and were cached forever.
4. Lists were limited to page 1 (15 rows).
5. Orders were not sorted.

## 3. Changes made (only inside `shop_customer/`)

| Commit | Files | Change |
|---|---|---|
| `f9bf05e` fix(mobile): read 409/422 errors and follow pagination | `lib/services/api_client.dart`, `test/api_client_test.dart` | `errorMessage` uses `message` when `errors` holds objects; new `getAll()` follows `last_page` / `meta.last_page`; envelope kept in `response.extra`; 4 tests |
| `a4742f7` fix(mobile): align products with API | `lib/models/product.dart`, `lib/services/catalog_service.dart`, `lib/providers/catalog_provider.dart`, `lib/views/products/product_details_screen.dart`, `test/widget_test.dart` | `stock=20` replaced by nullable `availableQuantity`; `getProduct()`; details screen shows availability and blocks sold-out; all pages |
| `f8bfa7c` fix(mobile): align orders with API | `lib/services/commerce_service.dart` | all pages, newest first, names for every item |
| `3f72bf2` fix(mobile): align notifications | `lib/services/notification_service.dart` | all pages via `meta.last_page` |
| `c83c697` fix(mobile): align auth user parsing | `lib/models/user_profile.dart` | `role` or `roles[]` (as the web app does) |
| (this commit) | `lib/providers/auth_provider.dart`, `SYNC_REPORT.md` | corrected the rate-limit comment (server counts per IP); this report |

No change to URLs, payloads, or `app_config.dart`: they already matched the backend and the web app.

## 4. Test results

- `flutter analyze`: **9 issues, all info/warning level and all already present before the sync** (`curly_braces_in_flow_control_structures`, `unnecessary_cast`, `unnecessary_import`, doc-comment HTML). None introduced.
- `flutter test`: **19 passed** (12 before, 7 added).
- **Live walkthrough** (all passed). It ran the app's real `AuthProvider`, `CatalogProvider`, `OrdersProvider` and `NotificationsProvider` against the running backend (`localhost:8000`) in a temporary test that was not committed.
  - register → logout → wrong password ("4 attempts left") → login → `me`
  - products list, filter by category (Mobile), search ("Key"), product details (`available_quantity` read)
  - place order (total 1350.0, item names shown) → order list → 409 message shown
  - notifications (title and message) → mark read (unread 1 → 0) → logout (token cleared)
- **Not tested:** the widgets. I could not drive the UI (Chrome or emulator) from this session. Only the screen logic behind them was run.
- **Backend runs with `INVENTORY_DRIVER=fake`, not gRPC.** See section 5, item 1. This means the **503 warehouse-down path and the real Go warehouse were not exercised**. The 409 path uses the same controller code but with fake stock (product 1 reports 1 available).

## 5. Not fixed / needs a teammate

**Backend (Ahmad / Reema)**
1. **The `app` Docker image does not build on a fresh pull.** `docker/php/Dockerfile` runs `install-extensions.sh` without `USER root`; the current `serversideup/php:8.3-fpm` runs as non-root, so it fails with `mkdir: cannot create directory '/usr/src/php': Permission denied`. Fix: add `USER root` before the `RUN` and `USER www-data` after it. Even with that fix, compiling grpc with `make -j2` took 20+ minutes for roughly 10% of the files on an 8-CPU / 3.7 GB Docker VM, so I stopped and used the fake driver. A prebuilt image would help.
2. The queue worker crashes at first start if `vendor/` is not installed yet (it exited with a missing `autoload.php` before `composer install`). It needs a restart after install. README order should say so.
3. `GET /products` has no stock, no category name, and an image that is only a relative path; the web app and the Postman proof both expect stock. Suggest returning `available_quantity` or a `category` object in the list, or documenting that only `/products/{id}` has it.
4. Product search is case-sensitive (`like` on PostgreSQL). Use `ilike`.
5. The user resource never returns `role` (it returns `roles` only if the relation is loaded, and there is no such relation). The web app falls back to the JWT.
6. `PATCH /notifications/{id}/read` with a non-UUID id returns a 500 with the SQL and a stack trace (`APP_DEBUG=true` in `.env.example`). It should be a 404.
7. Inconsistent response shapes: categories and `PATCH /orders/{id}/status` are not enveloped; 422 from `$request->validate()` is Laravel's default while `LoginRequest` uses the envelope; `RateLimitLogin` (the `{error:{...}}` shape) is not used and `shared/Exceptions/Handler` is not registered.
8. `app/Modules/Notifications/Routes/api.php` is fully commented out, and `Orders/Routes/api.php` and `Products/Routes/api.php` still use `auth:sanctum` / `role:admin`. They are dead code since `routes/api.php` defines the routes. The `Handler` class and `debug_role` in the 403 body are also leftovers.
9. The login throttle is per IP (Laravel default), not per email as the README and the Flutter comment said.
10. Order creation has no `shipping_address`, and order items have no product name, so every client needs extra requests for names.

**Web (Hazem)**
1. `product.stock` is never sent by the backend, so "in stock" filters and the out-of-stock badge will not work on live data (`NaN > 0` is false).
2. Order types expect `product_name`, `order_number`, `shipping_address`; the backend sends none of them.
3. `notificationsApi` expects `title` / `message` at the top level; the backend nests the text in `data.message`. No UI uses it yet.
4. Only page 1 of products and orders is loaded (15 rows); filtering is done on the client.
5. `console.log("products:", ...)` left in `useProductCatalog.ts`.

**Flutter (mine), left as is**
- Checkout collects name, phone and address but the API has no field for them, so they are not sent (the screen says it is a demo).
- Search sends a request on every keystroke (no debounce).
- `uuid` for idempotent retries is supported by the backend but not sent by the app (the web app does not send it either).

## 6. Branch note

The latest backend (`007987b`) is **not on `main`** (`main` is still `4787a53`). It lives only on `backend-ahmad` / `backend-reema`. Ask the team to merge it into `main` before you open your PR, otherwise `mobile-yara` is tested against a backend that `main` does not contain. `mobile-yara` already has the older `c4af866` from `backend-ahmad`, so the merge should be straightforward.

## 7. Push (not run)

```
git push origin mobile-yara
```
