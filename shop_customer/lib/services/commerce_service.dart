import '../models/cart_item.dart';
import '../models/shop_order.dart';
import 'api_client.dart';

class CommerceService {
  final ApiClient _api = ApiClient();
  final Map<String, String> _names = {};
  final Set<String> _unknown = {}; // product ids the API no longer has (deleted products)

  /// Order items carry only product_id, so the names come from GET /products.
  /// Loads every page when a name is missing; ids that are still missing are remembered.
  Future<void> _loadNames(Iterable<String> ids) async {
    bool missing() => ids.any((id) => !_names.containsKey(id) && !_unknown.contains(id));
    if (!missing()) return;
    try {
      for (final p in await _api.getAll('/products')) {
        _names['${p['id']}'] = '${p['name']}';
      }
    } catch (_) {
      return; // names are cosmetic: the screens fall back to "Product #id"
    }
    _unknown.addAll(ids.where((id) => !_names.containsKey(id)));
  }

  Future<List<ShopOrder>> getOrders() async {
    final rows = await _api.getAll('/orders');
    await _loadNames([
      for (final order in rows)
        for (final item in (order['items'] as List? ?? const [])) '${item['product_id']}',
    ]);
    final orders = rows
        .map((e) => ShopOrder.fromJson(Map<String, dynamic>.from(e as Map), productNames: _names))
        .toList();
    orders.sort((a, b) => b.createdAt.compareTo(a.createdAt)); // newest first
    return orders;
  }

  /// POST /orders {items: [{product_id, quantity}]} (same payload as the web app).
  /// Errors: 409 not enough stock, 503 warehouse down, 422 invalid items.
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
