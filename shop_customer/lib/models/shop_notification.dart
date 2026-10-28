import 'dart:convert';

class ShopNotification {
  const ShopNotification({
    required this.id,
    required this.title,
    required this.message,
    required this.createdAt,
    this.read = false,
  });
  final String id;
  final String title;
  final String message;
  final DateTime createdAt;
  final bool read;

  /// "Just now", "5 min ago", "3 h ago", "2 d ago"
  String get time {
    final diff = DateTime.now().difference(createdAt);
    if (diff.inMinutes < 1) return 'Just now';
    if (diff.inHours < 1) return '${diff.inMinutes} min ago';
    if (diff.inDays < 1) return '${diff.inHours} h ago';
    return '${diff.inDays} d ago';
  }

  factory ShopNotification.fromJson(Map<String, dynamic> json) {
    dynamic data = json['data'];
    // data can be a Map, a JSON string, or a JSON string inside a JSON string.
    for (var i = 0; i < 2 && data is String; i++) {
      try {
        data = jsonDecode(data as String);
      } catch (_) {
        data = {'message': data};
      }
    }
    final message = data is Map ? '${data['message'] ?? ''}' : '';
    return ShopNotification(
      id: '${json['id']}',
      title: message.toLowerCase().contains('status') ? 'Order status update' : 'Order update',
      message: message,
      createdAt: (DateTime.tryParse('${json['created_at']}') ?? DateTime.now()).toLocal(),
      read: json['read_at'] != null,
    );
  }

  ShopNotification copyWith({bool? read}) => ShopNotification(
        id: id, title: title, message: message, createdAt: createdAt, read: read ?? this.read,
      );
}
