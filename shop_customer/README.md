# Shop Customer — Flutter starter

A customer-facing e-commerce app starter built with Flutter + Provider. It runs in **mock mode** before the backend APIs are available and keeps API integration in separate service classes.

## Included
- Splash, login, registration
- Home/catalog, categories, search and category filtering
- Product details
- Cart, quantity changes, remove items, checkout
- Order confirmation, order history and order details
- Notifications and profile
- Provider state management and sample data
- Central Dio client, secure token storage, configurable API base URL
- API contract checklist

## Set up and run it
This ZIP contains the app source (`lib/`), `pubspec.yaml`, and documentation; it does not include generated Android/iOS platform folders.

1. In VS Code, open a terminal in the directory where you keep projects.
2. Create a clean Flutter shell project:
   ```bash
   flutter create shop_customer
   ```
3. Extract this ZIP. Copy its `lib` folder and `pubspec.yaml` into the newly created `shop_customer` folder, replacing the generated `lib` folder and `pubspec.yaml`. Also copy `README.md` and `API_CONTRACT.md`.
4. Open the `shop_customer` folder in VS Code.
5. Run:
   ```bash
   flutter pub get
   flutter run
   ```
6. The app starts in mock mode. Use any valid email format and a password of at least 8 characters to enter; registration is simulated. Do not use real credentials in mock mode.

## Before connecting APIs
Open `lib/core/config/app_config.dart`.
- Keep `useMockData = true` while backend APIs are unavailable.
- When the backend contract is agreed and implemented, set `useMockData = false`.
- Set `apiBaseUrl` to the reachable backend URL.
- Update the service methods in `lib/services/` to match the real routes and JSON response shapes.

For a physical Android phone, `localhost` means the phone itself. Use the development computer's LAN IP address, and ensure the phone and computer can reach each other. For Android emulator, the host machine is usually reachable at `10.0.2.2`. Use HTTPS for production. If testing plain HTTP in development, Android network security configuration may need to be adjusted deliberately.

## Architecture
- `models/`: typed data objects
- `services/`: mock implementations and API integration boundary
- `providers/`: UI state and actions
- `views/`: screens
- `core/`: config, theme and reusable widgets

## Important
This is a frontend starter, not a production commerce backend. The backend must enforce authentication, roles, stock validation, order ownership, and order totals. Never trust prices or totals calculated on the client.
