import 'package:flutter/foundation.dart';
import '../models/category.dart';
import '../models/product.dart';
import '../services/api_client.dart';
import '../services/catalog_service.dart';

class CatalogProvider extends ChangeNotifier {
  CatalogProvider(this._service);
  final CatalogService _service;
  List<Product> products = [];
  List<ProductCategory> _categories = [];
  bool isLoading = false;
  String? error;
  String selectedCategory = 'All';
  String search = '';

  /// The screens show these names as chips.
  List<String> get categories => ['All', ..._categories.map((c) => c.name)];

  Map<int, String> get _categoryNames => {for (final c in _categories) c.id: c.name};

  int? get _selectedCategoryId {
    for (final c in _categories) {
      if (c.name == selectedCategory) return c.id;
    }
    return null; // "All"
  }

  Future<void> load() async {
    isLoading = true;
    error = null;
    notifyListeners();
    try {
      if (_categories.isEmpty) {
        try {
          _categories = await _service.getCategories();
        } catch (_) {
          // Backend bug 3: the categories route returns 404 until fixed.
          // The app still works and shows only "All".
        }
      }
      products = await _service.getProducts(
        categoryId: _selectedCategoryId,
        search: search,
        categoryNames: _categoryNames,
      );
    } catch (e) {
      error = ApiClient.errorMessage(e);
    } finally {
      isLoading = false;
      notifyListeners();
    }
  }

  Future<void> setCategory(String category) async {
    selectedCategory = category;
    await load();
  }

  Future<void> setSearch(String value) async {
    search = value;
    await load();
  }
}
