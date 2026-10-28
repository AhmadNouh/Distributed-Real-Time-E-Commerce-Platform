import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import 'core/theme/app_theme.dart';
import 'providers/auth_provider.dart';
import 'providers/cart_provider.dart';
import 'providers/catalog_provider.dart';
import 'providers/orders_provider.dart';
import 'providers/notifications_provider.dart';
import 'services/auth_service.dart';
import 'services/catalog_service.dart';
import 'services/commerce_service.dart';
import 'services/notification_service.dart';
import 'views/splash/splash_screen.dart';

void main() {
  WidgetsFlutterBinding.ensureInitialized();
  runApp(const ShopCustomerApp());
}

class ShopCustomerApp extends StatelessWidget {
  const ShopCustomerApp({super.key});

  @override
  Widget build(BuildContext context) {
    return MultiProvider(
      providers: [
        ChangeNotifierProvider(create: (_) => AuthProvider(AuthService())),
        ChangeNotifierProvider(create: (_) => CatalogProvider(CatalogService())..load()),
        ChangeNotifierProvider(create: (_) => CartProvider()),
        ChangeNotifierProvider(create: (_) => OrdersProvider(CommerceService())),
        ChangeNotifierProvider(create: (_) => NotificationsProvider(NotificationService())),
      ],
      child: MaterialApp(
        title: 'Shop',
        debugShowCheckedModeBanner: false,
        theme: AppTheme.light,
        home: const SplashScreen(),
      ),
    );
  }
}
