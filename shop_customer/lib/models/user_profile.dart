class UserProfile {
  const UserProfile({required this.name, required this.email, this.role = 'customer'});
  final String name;
  final String email;
  final String role;

  factory UserProfile.fromJson(Map<String, dynamic> json) => UserProfile(
    name: json['name'] as String? ?? '',
    email: json['email'] as String? ?? '',
    role: _role(json),
  );

  /// The API sends no `role` today (only `roles` when the relation is loaded); the web app
  /// reads both, so do the same and default to a customer.
  static String _role(Map<String, dynamic> json) {
    final role = json['role'];
    if (role is String && role.isNotEmpty) return role;
    final roles = json['roles'];
    if (roles is List && roles.contains('admin')) return 'admin';
    return 'customer';
  }
}
