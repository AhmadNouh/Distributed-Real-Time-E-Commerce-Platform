class UserProfile {
  const UserProfile({required this.name, required this.email, this.role = 'customer'});
  final String name;
  final String email;
  final String role;

  factory UserProfile.fromJson(Map<String, dynamic> json) => UserProfile(
    name: json['name'] as String? ?? '',
    email: json['email'] as String? ?? '',
    role: json['role'] as String? ?? 'customer',
  );
}
