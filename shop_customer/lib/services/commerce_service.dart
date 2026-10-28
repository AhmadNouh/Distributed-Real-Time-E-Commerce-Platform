import '../core/config/app_config.dart';
import '../models/shop_order.dart';
import 'api_client.dart';

class CommerceService {
  final ApiClient _api = ApiClient();
  final List<ShopOrder> _mockOrders = [];

  Future<List<ShopOrder>> getOrders() async {
    if (AppConfig.useMockData) {
      await Future.delayed(const Duration(milliseconds: 250));
      return List.unmodifiable(_mockOrders.reversed.toList());
    }
    final response = await _api.dio.get('/api/orders');
    final data = response.data;
    final List list = data is List ? data : (data['data'] as List? ?? []);
    return list.map((e) => ShopOrder.fromJson(Map<String, dynamic>.from(e as Map))).toList();
  }

  Future<ShopOrder> createOrder({required double total, required String summary}) async {
    if (AppConfig.useMockData) {
      await Future.delayed(const Duration(milliseconds: 450));
      final order = ShopOrder(
        id: 'ORD-${DateTime.now().millisecondsSinceEpoch.toString().substring(7)}',
        createdAt: DateTime.now(), total: total, status: 'Pending', itemsSummary: summary,
      );
      _mockOrders.add(order);
      return order;
    }
    final response = await _api.dio.post('/api/orders', data: {'items_summary': summary});
    return ShopOrder.fromJson(Map<String, dynamic>.from(response.data as Map));
  }
}
