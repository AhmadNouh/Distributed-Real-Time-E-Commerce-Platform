import 'package:flutter/foundation.dart';
import '../models/shop_notification.dart';
import '../services/api_client.dart';
import '../services/notification_service.dart';

class NotificationsProvider extends ChangeNotifier {
  NotificationsProvider(this._service);
  final NotificationService _service;
  List<ShopNotification> notifications = [];
  bool isLoading = false;
  String? error;

  int get unreadCount => notifications.where((n) => !n.read).length;

  Future<void> load() async {
    isLoading = true;
    error = null;
    notifyListeners();
    try {
      notifications = await _service.getNotifications();
    } catch (e) {
      error = ApiClient.errorMessage(e);
    } finally {
      isLoading = false;
      notifyListeners();
    }
  }

  Future<void> markAsRead(ShopNotification item) async {
    if (item.read) return;
    try {
      await _service.markAsRead(item.id);
      notifications = [
        for (final n in notifications) n.id == item.id ? n.copyWith(read: true) : n,
      ];
      notifyListeners();
    } catch (_) {}
  }
}
