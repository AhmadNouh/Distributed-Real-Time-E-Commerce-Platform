import 'package:flutter/foundation.dart';
import '../models/shop_order.dart';
import '../services/commerce_service.dart';

class OrdersProvider extends ChangeNotifier {
  OrdersProvider(this._service);
  final CommerceService _service;
  List<ShopOrder> orders = [];
  bool isLoading = false;

  Future<void> load() async {
    isLoading = true; notifyListeners();
    try { orders = await _service.getOrders(); } finally { isLoading = false; notifyListeners(); }
  }

  Future<ShopOrder> checkout({required double total, required String summary}) async {
    final order = await _service.createOrder(total: total, summary: summary);
    await load();
    return order;
  }
}
