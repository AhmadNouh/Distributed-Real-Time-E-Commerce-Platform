import 'package:flutter/foundation.dart';
import '../models/cart_item.dart';
import '../models/product.dart';

class CartProvider extends ChangeNotifier {
  final List<CartItem> _items = [];
  List<CartItem> get items => List.unmodifiable(_items);
  int get itemCount => _items.fold(0, (sum, item) => sum + item.quantity);
  double get subtotal => _items.fold(0, (sum, item) => sum + item.total);
  double get deliveryFee => _items.isEmpty || subtotal >= 75 ? 0 : 5.5;
  double get total => subtotal + deliveryFee;

  void add(Product product) {
    final index = _items.indexWhere((i) => i.product.id == product.id);
    if (index >= 0) { _items[index].quantity++; } else { _items.add(CartItem(product: product)); }
    notifyListeners();
  }
  void increase(String id) { final i = _items.indexWhere((e) => e.product.id == id); if (i >= 0) _items[i].quantity++; notifyListeners(); }
  void decrease(String id) { final i = _items.indexWhere((e) => e.product.id == id); if (i >= 0) { if (_items[i].quantity > 1) _items[i].quantity--; else _items.removeAt(i); } notifyListeners(); }
  void remove(String id) { _items.removeWhere((e) => e.product.id == id); notifyListeners(); }
  void clear() { _items.clear(); notifyListeners(); }
}
