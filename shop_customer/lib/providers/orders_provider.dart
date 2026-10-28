import 'package:flutter/foundation.dart';
import '../models/cart_item.dart';
import '../models/shop_order.dart';
import '../services/api_client.dart';
import '../services/commerce_service.dart';

class OrdersProvider extends ChangeNotifier {
  OrdersProvider(this._service);
  final CommerceService _service;
  List<ShopOrder> orders = [];
  bool isLoading = false;
  String? error;

  Future<void> load() async {
    isLoading = true;
    error = null;
    notifyListeners();
    try {
      orders = await _service.getOrders();
    } catch (e) {
      error = ApiClient.errorMessage(e);
    } finally {
      isLoading = false;
      notifyListeners();
    }
  }

  Future<ShopOrder> checkout(List<CartItem> items) async {
    final order = await _service.createOrder(items);
    await load();
    return order;
  }
}
