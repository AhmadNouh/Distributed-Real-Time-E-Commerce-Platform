import '../models/category.dart';
import '../models/product.dart';
import 'api_client.dart';

class CatalogService {
  final ApiClient _api = ApiClient();

  Future<List<ProductCategory>> getCategories() async {
    final response = await _api.dio.get('/products/categories');
    return ApiClient.listFrom(response.data)
        .map((e) => ProductCategory.fromJson(Map<String, dynamic>.from(e as Map)))
        .toList();
  }

  Future<List<Product>> getProducts({
    int? categoryId,
    String search = '',
    Map<int, String> categoryNames = const {},
  }) async {
    final response = await _api.dio.get('/products', queryParameters: {
      if (categoryId != null) 'category_id': categoryId,
      if (search.trim().isNotEmpty) 'search': search.trim(),
      'page': 1,
    });
    return ApiClient.listFrom(response.data)
        .map((e) => Product.fromJson(Map<String, dynamic>.from(e as Map), categoryNames: categoryNames))
        .toList();
  }
}
