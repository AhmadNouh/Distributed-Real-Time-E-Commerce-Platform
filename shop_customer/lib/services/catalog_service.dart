import '../core/config/app_config.dart';
import '../models/product.dart';
import 'api_client.dart';

class CatalogService {
  final ApiClient _api = ApiClient();

  static const _products = <Product>[
    Product(id: 'p1', name: 'Everyday Ceramic Mug', description: 'A minimal ceramic mug for slow mornings. Durable, balanced and made for everyday rituals.', category: 'Home', price: 18, imageUrl: 'https://images.unsplash.com/photo-1514228742587-6b1558fcca3d?w=800', badge: 'BESTSELLER'),
    Product(id: 'p2', name: 'Soft Knit Throw', description: 'A soft, textured throw that adds warmth and calm to your favorite corner.', category: 'Home', price: 46, imageUrl: 'https://images.unsplash.com/photo-1600210492486-724fe5c67fb0?w=800'),
    Product(id: 'p3', name: 'Everyday Tote', description: 'A roomy everyday tote with a clean silhouette and sturdy handles.', category: 'Accessories', price: 32, imageUrl: 'https://images.unsplash.com/photo-1544816155-12df9643f363?w=800', badge: 'POPULAR'),
    Product(id: 'p4', name: 'Amber Glass Bottle', description: 'A reusable glass bottle with a simple, timeless shape.', category: 'Lifestyle', price: 24, imageUrl: 'https://images.unsplash.com/photo-1602143407151-7111542de6e8?w=800'),
    Product(id: 'p5', name: 'Desk Organizer', description: 'Keep your daily essentials tidy with this compact desk organizer.', category: 'Lifestyle', price: 29, imageUrl: 'https://images.unsplash.com/photo-1494438639946-1ebd1d20bf85?w=800'),
    Product(id: 'p6', name: 'Linen Cushion', description: 'A relaxed linen cushion cover to bring texture to your space.', category: 'Home', price: 26, imageUrl: 'https://images.unsplash.com/photo-1584100936595-c0654b55a2e2?w=800'),
    Product(id: 'p7', name: 'Classic Watch', description: 'A versatile, understated watch designed to complement daily looks.', category: 'Accessories', price: 68, imageUrl: 'https://images.unsplash.com/photo-1524805444758-089113d48a6d?w=800'),
    Product(id: 'p8', name: 'Daily Journal', description: 'A beautifully simple notebook for plans, thoughts and ideas.', category: 'Stationery', price: 14, imageUrl: 'https://images.unsplash.com/photo-1531346878377-a5be20888e57?w=800'),
  ];

  Future<List<Product>> getProducts({String? category, String search = ''}) async {
    if (AppConfig.useMockData) {
      await Future.delayed(const Duration(milliseconds: 250));
      return _products.where((p) {
        final matchesCategory = category == null || category == 'All' || p.category == category;
        final q = search.trim().toLowerCase();
        final matchesSearch = q.isEmpty || p.name.toLowerCase().contains(q) || p.description.toLowerCase().contains(q);
        return matchesCategory && matchesSearch;
      }).toList();
    }
    final response = await _api.dio.get('/api/products', queryParameters: {
      if (category != null && category != 'All') 'category': category,
      if (search.trim().isNotEmpty) 'search': search.trim(),
      'page': 1, 'per_page': 20,
    });
    final data = response.data;
    final List list = data is List ? data : (data['data'] as List? ?? []);
    return list.map((e) => Product.fromJson(Map<String, dynamic>.from(e as Map))).toList();
  }
}
