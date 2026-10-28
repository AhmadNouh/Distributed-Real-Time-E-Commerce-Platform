import 'package:flutter/foundation.dart';
import '../models/product.dart';
import '../services/catalog_service.dart';

class CatalogProvider extends ChangeNotifier {
  CatalogProvider(this._service);
  final CatalogService _service;
  List<Product> products = [];
  bool isLoading = false;
  String? error;
  String selectedCategory = 'All';
  String search = '';

  List<String> get categories => ['All', ...{for (final p in products) p.category}];

  Future<void> load() async {
    isLoading = true; error = null; notifyListeners();
    try { products = await _service.getProducts(category: selectedCategory, search: search); }
    catch (_) { error = 'Could not load products. Check your connection and try again.'; }
    finally { isLoading = false; notifyListeners(); }
  }

  Future<void> setCategory(String category) async { selectedCategory = category; await load(); }
  Future<void> setSearch(String value) async { search = value; await load(); }
}
