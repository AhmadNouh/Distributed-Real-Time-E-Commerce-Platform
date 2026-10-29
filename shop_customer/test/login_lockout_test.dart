import 'package:dio/dio.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:shop_customer/models/user_profile.dart';
import 'package:shop_customer/providers/auth_provider.dart';
import 'package:shop_customer/services/auth_service.dart';

/// Pretends to be the server: answers with the given status code and counts calls.
class FakeAuthService extends AuthService {
  FakeAuthService(this.status, {this.headers = const {}});
  final int status;
  final Map<String, List<String>> headers;
  int calls = 0;

  @override
  Future<AuthResult> login(String email, String password) async {
    calls++;
    if (status == 200) {
      return const AuthResult(user: UserProfile(name: 'Alice', email: 'alice@test.com'));
    }
    final request = RequestOptions(path: '/auth/login');
    throw DioException(
      requestOptions: request,
      response: Response(
        requestOptions: request,
        statusCode: status,
        headers: Headers.fromMap(headers),
        data: {'success': false, 'message': status == 401 ? 'Invalid credentials' : 'Too Many Attempts.'},
      ),
      type: DioExceptionType.badResponse,
    );
  }
}

void main() {
  test('shows how many attempts are left after a wrong password', () async {
    final auth = AuthProvider(FakeAuthService(401));
    expect(await auth.login('a@test.com', 'wrong'), isFalse);
    expect(auth.error, 'Invalid credentials. 4 attempts left.');
    expect(auth.lockSecondsRemaining('a@test.com'), 0);
  });

  test('locks the 5th wrong attempt and stops calling the server', () async {
    final service = FakeAuthService(401);
    final auth = AuthProvider(service);
    for (var i = 0; i < AuthProvider.maxLoginAttempts; i++) {
      await auth.login('a@test.com', 'wrong');
    }
    expect(service.calls, 5);
    expect(auth.lockSecondsRemaining('a@test.com'), inInclusiveRange(55, 61));
    expect(auth.error, startsWith('Too many login attempts.'));

    expect(await auth.login('A@test.com ', 'wrong'), isFalse); // same email, different spelling
    expect(service.calls, 5, reason: 'a locked email must not reach the server');
    expect(auth.lockSecondsRemaining('other@test.com'), 0, reason: 'other emails are not locked');
  });

  test('follows the server 429 and its Retry-After header', () async {
    final auth = AuthProvider(FakeAuthService(429, headers: {'retry-after': ['42']}));
    expect(await auth.login('a@test.com', 'x'), isFalse);
    expect(auth.lockSecondsRemaining('a@test.com'), inInclusiveRange(41, 42));
    expect(auth.error, 'Too many login attempts. Try again in 42s.');
  });

  test('uses 60 seconds when the server sends no Retry-After', () async {
    final auth = AuthProvider(FakeAuthService(429));
    await auth.login('a@test.com', 'x');
    expect(auth.lockSecondsRemaining('a@test.com'), inInclusiveRange(59, 60));
  });

  test('a successful login clears the failed attempts', () async {
    final auth = AuthProvider(FakeAuthService(200));
    expect(await auth.login('a@test.com', 'right'), isTrue);
    expect(auth.user?.name, 'Alice');
    expect(auth.lockSecondsRemaining('a@test.com'), 0);
  });
}
