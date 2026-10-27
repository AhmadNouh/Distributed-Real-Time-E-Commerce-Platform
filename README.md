# E-Commerce REST API — Modular Monolith

## Architecture
4 modules (Users, Products, Orders, Notifications) under `app/Modules/`. Shared cross-cutting code under `app/Shared/`. Module isolation: no direct Eloquent references across modules; communication via Service contracts or domain Events.

## Inventory Boundary (Requirement 2 Ready)
Orders uses `App\Modules\Orders\Contracts\InventoryClient`. Swap `fake` → `grpc` via `config/inventory.php` / `.env` (`INVENTORY_DRIVER`). `GrpcInventoryClient` placeholder throws `NotImplementedException`.

## Endpoints
- Auth: POST /api/v1/auth/register, POST /api/v1/auth/login, POST /api/v1/auth/logout, GET /api/v1/me
- Products: GET /api/v1/products, GET /api/v1/products/{id}, POST/PUT/DELETE (admin)
- Orders: POST /api/v1/orders, GET /api/v1/orders, GET /api/v1/orders/{id}, PATCH status (admin)
- Notifications: GET /api/v1/notifications, PATCH /api/v1/notifications/{id}/read

## Default Credentials
Admin: admin@store.test / password123
Customers: alice@test.com / bob@test.com / password123
