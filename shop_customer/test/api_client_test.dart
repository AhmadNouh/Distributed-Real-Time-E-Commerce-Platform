import 'dart:convert';
import 'dart:typed_data';
import 'package:dio/dio.dart';
import 'package:flutter/services.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:shop_customer/services/api_client.dart';

class _JsonAdapter implements HttpClientAdapter {
  _JsonAdapter(this.body);
  final Object? body;

  @override
  Future<ResponseBody> fetch(RequestOptions options, Stream<Uint8List>? requestStream, Future<void>? cancelFuture) async =>
      ResponseBody.fromString(jsonEncode(body), 200, headers: {
        Headers.contentTypeHeader: ['application/json'],
      });

  @override
  void close({bool force = false}) {}
}

void main() {
  TestWidgetsFlutterBinding.ensureInitialized();

  setUp(() {
    // flutter_secure_storage has no platform in tests: pretend no token is saved.
    TestDefaultBinaryMessengerBinding.instance.defaultBinaryMessenger.setMockMethodCallHandler(
      const MethodChannel('plugins.it_nomads.com/flutter_secure_storage'),
      (_) async => null,
    );
  });

  Future<dynamic> get(Object? body) async {
    final dio = ApiClient().dio;
    dio.httpClientAdapter = _JsonAdapter(body);
    return (await dio.get('/anything')).data;
  }

  test('unwraps the {success, data, message} envelope', () async {
    final data = await get({
      'success': true,
      'data': {'token': 'abc', 'user': {'name': 'Alice'}},
      'message': 'Login successful',
      'errors': null,
    });
    expect(data, {'token': 'abc', 'user': {'name': 'Alice'}});
  });

  test('a wrapped paginated list still gives the product rows', () async {
    final data = await get({
      'success': true,
      'data': {'current_page': 1, 'data': [{'id': 1}, {'id': 2}]},
      'message': 'ok',
    });
    expect(ApiClient.listFrom(data), hasLength(2));
  });

  test('a wrapped plain list (notifications) works', () async {
    final data = await get({'success': true, 'data': [{'id': 'x'}], 'meta': {'total': 1}});
    expect(ApiClient.listFrom(data), hasLength(1));
  });

  test('leaves unwrapped responses (orders, categories) alone', () async {
    final orders = {'current_page': 1, 'data': [{'id': 7}]};
    expect(await get(orders), orders);
    expect(await get([{'id': 1, 'name': 'Laptop'}]), [{'id': 1, 'name': 'Laptop'}]);
  });

  test('reads error text from the new error shape', () {
    final request = RequestOptions(path: '/x');
    final error = DioException(
      requestOptions: request,
      response: Response(requestOptions: request, statusCode: 401, data: {'success': false, 'data': null, 'message': 'Invalid credentials', 'errors': null}),
    );
    expect(ApiClient.errorMessage(error), 'Invalid credentials');
  });

  test('a 409 out-of-stock answer shows its message, not the raw shortage object', () {
    final request = RequestOptions(path: '/orders');
    final error = DioException(
      requestOptions: request,
      response: Response(requestOptions: request, statusCode: 409, data: {
        'success': false,
        'data': null,
        'message': 'Insufficient stock for this order. Laptop: requested 5, available 1.',
        'errors': {
          'stock': [
            {'product_id': 1, 'requested': 5, 'available': 1, 'name': 'Laptop'},
          ],
        },
      }),
    );
    expect(ApiClient.errorMessage(error), 'Insufficient stock for this order. Laptop: requested 5, available 1.');
  });

  test('a 422 answer shows the first field error', () {
    final request = RequestOptions(path: '/auth/register');
    final error = DioException(
      requestOptions: request,
      response: Response(requestOptions: request, statusCode: 422, data: {
        'message': 'The email has already been taken.',
        'errors': {
          'email': ['The email has already been taken.'],
        },
      }),
    );
    expect(ApiClient.errorMessage(error), 'The email has already been taken.');
  });

  test('getAll follows the paginator (products, orders)', () async {
    final dio = ApiClient().dio;
    dio.httpClientAdapter = _PagedAdapter();
    final rows = await ApiClient().getAll('/products');
    expect(rows.map((r) => r['id']), [1, 2, 3]);
  });

  test('getAll follows meta.last_page (notifications)', () async {
    final dio = ApiClient().dio;
    dio.httpClientAdapter = _PagedAdapter(useMeta: true);
    final rows = await ApiClient().getAll('/notifications');
    expect(rows.map((r) => r['id']), [1, 2, 3]);
  });
}

/// Two pages: ids 1-2 on page 1, id 3 on page 2.
class _PagedAdapter implements HttpClientAdapter {
  _PagedAdapter({this.useMeta = false});
  final bool useMeta;

  @override
  Future<ResponseBody> fetch(RequestOptions options, Stream<Uint8List>? requestStream, Future<void>? cancelFuture) async {
    final page = options.queryParameters['page'] as int;
    final rows = page == 1 ? [{'id': 1}, {'id': 2}] : [{'id': 3}];
    final body = useMeta
        ? {'success': true, 'data': rows, 'meta': {'current_page': page, 'last_page': 2}}
        : {'success': true, 'data': {'current_page': page, 'last_page': 2, 'data': rows}, 'message': 'ok'};
    return ResponseBody.fromString(jsonEncode(body), 200, headers: {
      Headers.contentTypeHeader: ['application/json'],
    });
  }

  @override
  void close({bool force = false}) {}
}
