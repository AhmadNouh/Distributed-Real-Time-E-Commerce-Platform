class OrderLine {
  const OrderLine({required this.productId, required this.quantity, required this.price});
  final String productId;
  final int quantity;
  final double price;

  factory OrderLine.fromJson(Map<String, dynamic> json) => OrderLine(
        productId: '${json['product_id']}',
        quantity: int.tryParse('${json['quantity']}') ?? 1,
        price: double.tryParse('${json['price'] ?? 0}') ?? 0,
      );
}

class ShopOrder {
  const ShopOrder({
    required this.id,
    required this.createdAt,
    required this.total,
    required this.status,
    required this.itemsSummary,
    this.items = const [],
  });
  final String id;
  final DateTime createdAt;
  final double total;
  final String status;
  final String itemsSummary;
  final List<OrderLine> items;

  /// [productNames] maps product id -> name, because order items carry only the id.
  factory ShopOrder.fromJson(Map<String, dynamic> json, {Map<String, String> productNames = const {}}) {
    final items = (json['items'] as List? ?? [])
        .map((e) => OrderLine.fromJson(Map<String, dynamic>.from(e as Map)))
        .toList();
    final summary = items
        .map((i) => '${productNames[i.productId] ?? 'Product #${i.productId}'} x${i.quantity}')
        .join(', ');
    final rawStatus = json['status'] as String? ?? 'pending';
    return ShopOrder(
      id: '${json['id']}',
      createdAt: (DateTime.tryParse('${json['created_at']}') ?? DateTime.now()).toLocal(),
      total: double.tryParse('${json['total'] ?? 0}') ?? 0,
      status: rawStatus.isEmpty ? 'Pending' : rawStatus[0].toUpperCase() + rawStatus.substring(1),
      itemsSummary: json['items_summary'] as String? ?? summary,
      items: items,
    );
  }
}
