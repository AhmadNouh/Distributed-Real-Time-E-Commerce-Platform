import '../models/shop_notification.dart';
import 'api_client.dart';

class NotificationService {
  final ApiClient _api = ApiClient();

  Future<List<ShopNotification>> getNotifications() async {
    final response = await _api.dio.get('/notifications');
    final list = ApiClient.listFrom(response.data)
        .map((e) => ShopNotification.fromJson(Map<String, dynamic>.from(e as Map)))
        .toList();
    list.sort((a, b) => b.createdAt.compareTo(a.createdAt)); // newest first
    return list;
  }

  Future<void> markAsRead(String id) async {
    await _api.dio.patch('/notifications/$id/read');
  }
}
