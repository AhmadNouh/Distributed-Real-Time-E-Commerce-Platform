import 'package:dio/dio.dart';
import 'package:flutter/foundation.dart';
import '../models/user_profile.dart';
import '../services/api_client.dart';
import '../services/auth_service.dart';

class AuthProvider extends ChangeNotifier {
  AuthProvider(this._service);
  final AuthService _service;
  UserProfile? user;
  bool isLoading = false;
  String? error;

  /// Mirrors the server rule on POST /auth/login: 5 attempts per minute (the server counts per
  /// IP, the app counts per email and also follows the server 429 + Retry-After).
  static const int maxLoginAttempts = 5;
  static const Duration loginWindow = Duration(seconds: 60);

  final Map<String, List<DateTime>> _failedLogins = {};
  final Map<String, DateTime> _lockedUntil = {};
  Object? _lastException;

  bool get isAuthenticated => user != null;

  static String lockMessage(int seconds) => 'Too many login attempts. Try again in ${seconds}s.';

  String _key(String email) => email.trim().toLowerCase();

  /// Seconds left before [email] may try to log in again, 0 when not locked.
  int lockSecondsRemaining(String email) {
    final key = _key(email);
    final until = _lockedUntil[key];
    if (until == null) return 0;
    final seconds = (until.difference(DateTime.now()).inMilliseconds / 1000).ceil();
    if (seconds <= 0) {
      _lockedUntil.remove(key);
      _failedLogins.remove(key);
      return 0;
    }
    return seconds;
  }

  Future<bool> login(String email, String password) async {
    final wait = lockSecondsRemaining(email);
    if (wait > 0) {
      error = lockMessage(wait);
      notifyListeners();
      return false;
    }
    final ok = await _run(() async {
      final result = await _service.login(email, password);
      user = result.user;
    });
    final key = _key(email);
    if (ok) {
      _failedLogins.remove(key);
      _lockedUntil.remove(key);
    } else {
      _recordLoginFailure(key);
      notifyListeners();
    }
    return ok;
  }

  void _recordLoginFailure(String key) {
    final e = _lastException;
    if (e is! DioException) return;
    final status = e.response?.statusCode;
    if (status == 429) {
      // The server already locked this email: follow its Retry-After.
      final seconds = ApiClient.retryAfterSeconds(e);
      _lockedUntil[key] = DateTime.now().add(Duration(seconds: seconds));
      error = lockMessage(seconds);
      return;
    }
    if (status != 401) return;
    final now = DateTime.now();
    final recent = [
      for (final t in _failedLogins[key] ?? const <DateTime>[])
        if (now.difference(t) < loginWindow) t,
      now,
    ];
    _failedLogins[key] = recent;
    if (recent.length >= maxLoginAttempts) {
      final until = recent.first.add(loginWindow);
      _lockedUntil[key] = until;
      error = lockMessage(until.difference(now).inSeconds + 1);
    } else {
      final left = maxLoginAttempts - recent.length;
      final reason = (error ?? 'Invalid credentials').replaceAll(RegExp(r'\.$'), '');
      error = '$reason. $left attempt${left == 1 ? '' : 's'} left.';
    }
  }

  Future<bool> register(String name, String email, String password) => _run(() async {
        final result = await _service.register(name, email, password);
        user = result.user;
      });

  /// Returns true when a saved token is still valid.
  Future<bool> restoreSession() async {
    user = await _service.restoreSession();
    notifyListeners();
    return user != null;
  }

  Future<bool> _run(Future<void> Function() action) async {
    isLoading = true;
    error = null;
    _lastException = null;
    notifyListeners();
    try {
      await action();
      return true;
    } catch (e) {
      _lastException = e;
      error = ApiClient.errorMessage(e);
      return false;
    } finally {
      isLoading = false;
      notifyListeners();
    }
  }

  Future<void> logout() async {
    await _service.logout();
    user = null;
    notifyListeners();
  }
}
