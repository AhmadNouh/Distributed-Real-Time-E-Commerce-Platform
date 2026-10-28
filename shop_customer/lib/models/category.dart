class ProductCategory {
  const ProductCategory({required this.id, required this.name});
  final int id;
  final String name;

  factory ProductCategory.fromJson(Map<String, dynamic> json) => ProductCategory(
        id: int.tryParse('${json['id']}') ?? 0,
        name: json['name'] as String? ?? '',
      );
}
