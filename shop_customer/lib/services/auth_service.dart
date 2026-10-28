import '../core/config/app_config.dart';
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
    if (AppConfig.useMockData) {
      await Future.delayed(const Duration(milliseconds: 450));
      return AuthResult(user: UserProfile(name: email.split('@').first, email: email), token: 'mock-token');
    }
    final response = await _api.dio.post('/api/auth/login', data: {'email': email, 'password': password});
    final data = Map<String, dynamic>.from(response.data as Map);
    final user = UserProfile.fromJson(Map<String, dynamic>.from(data['user'] as Map));
    final token = data['access_token'] as String?;
    if (token != null) await _storage.saveToken(token);
    return AuthResult(user: user, token: token);
  }

  Future<AuthResult> register(String name, String email, String password) async {
    if (AppConfig.useMockData) {
      await Future.delayed(const Duration(milliseconds: 450));
      return AuthResult(user: UserProfile(name: name, email: email), token: 'mock-token');
    }
    final response = await _api.dio.post('/api/auth/register', data: {'name': name, 'email': email, 'password': password});
    final data = Map<String, dynamic>.from(response.data as Map);
    final user = UserProfile.fromJson(Map<String, dynamic>.from(data['user'] as Map));
    final token = data['access_token'] as String?;
    if (token != null) await _storage.saveToken(token);
    return AuthResult(user: user, token: token);
  }

  Future<void> logout() async {
    if (!AppConfig.useMockData) {
      try { await _api.dio.post('/api/auth/logout'); } catch (_) {}
    }
    await _storage.clearToken();
  }
}
