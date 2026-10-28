# API contract to agree with the backend team

These are proposed routes, not confirmed routes. Agree on route names, request fields, status codes and JSON schemas with the backend developers before changing mock mode.

| Method | Proposed endpoint | Purpose |
|---|---|---|
| POST | `/api/auth/register` | Register customer |
| POST | `/api/auth/login` | Login; return access token and user |
| GET | `/api/categories` | List categories |
| GET | `/api/products?page=1&per_page=10&category_id=...&search=...` | Product catalog, filtering and pagination |
| GET | `/api/products/{id}` | Product details |
| GET | `/api/cart` | Get current customer's cart |
| POST | `/api/cart/items` | Add `{ product_id, quantity }` |
| PATCH | `/api/cart/items/{itemId}` | Update `{ quantity }` |
| DELETE | `/api/cart/items/{itemId}` | Remove cart item |
| POST | `/api/orders` | Create order from server-validated cart |
| GET | `/api/orders` | Current customer's orders |
| GET | `/api/orders/{id}` | One order owned by current customer |
| GET | `/api/notifications` | Current customer's notifications |
| GET | `/api/profile` | Current customer profile |
| POST | `/api/auth/logout` | Optional server-side logout/revocation |

Expected login response (example only):
```json
{
  "access_token": "token",
  "token_type": "Bearer",
  "user": { "id": 12, "name": "Customer", "email": "customer@example.com", "role": "customer" }
}
```

Integration checklist:
- [ ] Confirm the base URL and API prefix.
- [ ] Confirm JSON field names and pagination format.
- [ ] Confirm whether product images are absolute URLs.
- [ ] Confirm whether cart items are server-side or local until checkout.
- [ ] Confirm order creation request and allowed status transitions.
- [ ] Confirm JWT expiry and refresh-token behavior.
- [ ] Verify that customers can only read their own orders and notifications.
- [ ] Verify admin-only product mutations are rejected for customer tokens.
- [ ] Use HTTPS in production; never hardcode secrets.
