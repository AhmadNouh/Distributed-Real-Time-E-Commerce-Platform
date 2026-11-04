import '../core/config/app_config.dart';

class Product {
  const Product({
    required this.id,
    required this.name,
    required this.description,
    required this.category,
    required this.price,
    required this.imageUrl,
    this.categoryId,
    this.badge,
    this.availableQuantity,
  });
  final String id;
  final String name;
  final String description;
  final String category;
  final int? categoryId;
  final double price;
  final String imageUrl;
  final String? badge;

  /// Units in the warehouse. Only GET /products/{id} sends it (as "available_quantity");
  /// the list does not, and it is null too when the warehouse is down. null means "unknown".
  final int? availableQuantity;
  bool get isOutOfStock => availableQuantity != null && availableQuantity! <= 0;

  /// [categoryNames] maps category_id -> name, because the API sends only the id.
  factory Product.fromJson(Map<String, dynamic> json, {Map<int, String> categoryNames = const {}}) {
    final categoryId = int.tryParse('${json['category_id']}');
    return Product(
      id: '${json['id']}',
      name: json['name'] as String? ?? '',
      description: json['description'] as String? ?? '',
      categoryId: categoryId,
      category: json['category'] is Map
          ? (json['category']['name'] as String? ?? '')
          : (categoryNames[categoryId] ?? ''),
      price: double.tryParse('${json['price'] ?? 0}') ?? 0,
      imageUrl: _imageUrl(json['image_url'] ?? json['image']),
      badge: json['badge'] as String?,
      availableQuantity: int.tryParse('${json['available_quantity'] ?? json['stock'] ?? ''}'),
    );
  }

  /// The API sends "products/x.jpg"; the app needs "http://.../storage/products/x.jpg".
  static String _imageUrl(dynamic value) {
    final path = value?.toString() ?? '';
    if (path.isEmpty || path.startsWith('http')) return path;
    return '${AppConfig.storageUrl}/$path';
  }

  Map<String, dynamic> toJson() => {
        'id': id, 'name': name, 'description': description, 'category': category,
        'category_id': categoryId, 'price': price, 'image_url': imageUrl,
        'badge': badge, 'available_quantity': availableQuantity,
      };
}
