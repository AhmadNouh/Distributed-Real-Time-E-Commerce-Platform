import '../models/cart_item.dart';
import '../models/shop_order.dart';
import 'api_client.dart';

class CommerceService {
  final ApiClient _api = ApiClient();
  Map<String, String>? _names;

  /// Product id -> name, loaded once from GET /products.
  Future<Map<String, String>> _productNames() async {
    if (_names != null) return _names!;
    try {
      final response = await _api.dio.get('/products', queryParameters: {'page': 1});
      _names = {
        for (final p in ApiClient.listFrom(response.data)) '${p['id']}': '${p['name']}',
      };
    } catch (_) {
      _names = {};
    }
    return _names!;
  }

  Future<List<ShopOrder>> getOrders() async {
    final names = await _productNames();
    final response = await _api.dio.get('/orders');
    return ApiClient.listFrom(response.data)
        .map((e) => ShopOrder.fromJson(Map<String, dynamic>.from(e as Map), productNames: names))
        .toList();
  }

  Future<ShopOrder> createOrder(List<CartItem> cartItems) async {
    final response = await _api.dio.post('/orders', data: {
      'items': [
        for (final item in cartItems)
          {'product_id': int.parse(item.product.id), 'quantity': item.quantity},
      ],
    });
    final names = {for (final item in cartItems) item.product.id: item.product.name};
    return ShopOrder.fromJson(Map<String, dynamic>.from(response.data as Map), productNames: names);
  }
}
