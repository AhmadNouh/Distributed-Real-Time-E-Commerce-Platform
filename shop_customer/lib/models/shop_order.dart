class ShopOrder {
  const ShopOrder({required this.id, required this.createdAt, required this.total, required this.status, required this.itemsSummary});
  final String id;
  final DateTime createdAt;
  final double total;
  final String status;
  final String itemsSummary;

  factory ShopOrder.fromJson(Map<String, dynamic> json) => ShopOrder(
    id: '${json['id']}',
    createdAt: DateTime.tryParse('${json['created_at']}') ?? DateTime.now(),
    total: double.tryParse('${json['total'] ?? 0}') ?? 0,
    status: json['status'] as String? ?? 'Pending',
    itemsSummary: json['items_summary'] as String? ?? '',
  );
}
