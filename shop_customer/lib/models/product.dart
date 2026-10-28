class Product {
  const Product({
    required this.id, required this.name, required this.description,
    required this.category, required this.price, required this.imageUrl,
    this.badge, this.stock = 20,
  });
  final String id;
  final String name;
  final String description;
  final String category;
  final double price;
  final String imageUrl;
  final String? badge;
  final int stock;

  factory Product.fromJson(Map<String, dynamic> json) => Product(
    id: '${json['id']}',
    name: json['name'] as String? ?? '',
    description: json['description'] as String? ?? '',
    category: json['category'] is Map ? (json['category']['name'] as String? ?? '') : (json['category'] as String? ?? ''),
    price: double.tryParse('${json['price'] ?? 0}') ?? 0,
    imageUrl: json['image_url'] as String? ?? json['image'] as String? ?? '',
    badge: json['badge'] as String?,
    stock: int.tryParse('${json['stock'] ?? 0}') ?? 0,
  );

  Map<String, dynamic> toJson() => {
    'id': id, 'name': name, 'description': description, 'category': category,
    'price': price, 'image_url': imageUrl, 'badge': badge, 'stock': stock,
  };
}
