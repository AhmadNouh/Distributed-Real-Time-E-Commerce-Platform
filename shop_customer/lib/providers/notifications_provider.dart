import 'package:flutter/foundation.dart';

class ShopNotification {
  const ShopNotification({required this.title, required this.message, required this.time, this.read = false});
  final String title;
  final String message;
  final String time;
  final bool read;
}

class NotificationsProvider extends ChangeNotifier {
  final List<ShopNotification> notifications = const [
    ShopNotification(title: 'Welcome to Shop', message: 'Your little corner for thoughtful finds is ready.', time: 'Just now'),
    ShopNotification(title: 'Delivery updates', message: 'Your order status will appear here after checkout.', time: 'Today'),
    ShopNotification(title: 'A little reminder', message: 'Discover new pieces selected for everyday living.', time: 'Yesterday'),
  ];
}
