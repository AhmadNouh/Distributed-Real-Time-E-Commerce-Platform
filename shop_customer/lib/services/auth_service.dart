import '../models/user_profile.dart';
import 'api_client.dart';
import 'token_storage_service.dart';

class AuthResult {
  const AuthResult({required this.user, this.token});
  final UserProfile user;
  final String? token;
}

class AuthService {
  final ApiClient _api = ApiClient();
  final TokenStorageService _storage = TokenStorageService();

  Future<AuthResult> login(String email, String password) async {
    final response = await _api.dio.post('/auth/login', data: {
      'email': email,
      'password': password,
    });
    return _saveTokenAndLoadUser(response.data);
  }

  Future<AuthResult> register(String name, String email, String password) async {
    final response = await _api.dio.post('/auth/register', data: {
      'name': name,
      'email': email,
      'password': password,
      'password_confirmation': password,
    });
    return _saveTokenAndLoadUser(response.data);
  }

  /// GET /auth/me returns the logged-in user.
  Future<UserProfile> me() async {
    final response = await _api.dio.get('/auth/me');
    return UserProfile.fromJson(Map<String, dynamic>.from(response.data as Map));
  }

  /// Called by the splash screen: logs in again with the saved token.
  Future<UserProfile?> restoreSession() async {
    final token = await _storage.readToken();
    if (token == null || token.isEmpty) return null;
    try {
      return await me();
    } catch (_) {
      await _storage.clearToken();
      return null;
    }
  }

  Future<void> logout() async {
    try {
      await _api.dio.post('/auth/logout');
    } catch (_) {}
    await _storage.clearToken();
  }

  Future<AuthResult> _saveTokenAndLoadUser(dynamic data) async {
    final token = (data as Map)['token'] as String?;
    if (token == null || token.isEmpty) {
      throw Exception('The server did not return a token');
    }
    await _storage.saveToken(token);
    final user = await me();
    return AuthResult(user: user, token: token);
  }
}
