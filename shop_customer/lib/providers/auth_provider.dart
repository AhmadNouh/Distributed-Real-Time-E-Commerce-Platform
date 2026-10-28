import 'package:flutter/foundation.dart';
import '../models/user_profile.dart';
import '../services/auth_service.dart';

class AuthProvider extends ChangeNotifier {
  AuthProvider(this._service);
  final AuthService _service;
  UserProfile? user;
  bool isLoading = false;
  String? error;

  bool get isAuthenticated => user != null;

  Future<bool> login(String email, String password) => _run(() async {
    final result = await _service.login(email, password);
    user = result.user;
  });

  Future<bool> register(String name, String email, String password) => _run(() async {
    final result = await _service.register(name, email, password);
    user = result.user;
  });

  Future<bool> _run(Future<void> Function() action) async {
    isLoading = true; error = null; notifyListeners();
    try { await action(); return true; }
    catch (e) { error = 'Something went wrong. Please try again.'; return false; }
    finally { isLoading = false; notifyListeners(); }
  }

  Future<void> logout() async {
    await _service.logout(); user = null; notifyListeners();
  }
}
