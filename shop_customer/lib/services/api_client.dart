import 'package:dio/dio.dart';
import '../core/config/app_config.dart';
import 'token_storage_service.dart';

class ApiClient {
  ApiClient._internal() {
    dio = Dio(BaseOptions(
      baseUrl: AppConfig.apiBaseUrl,
      connectTimeout: AppConfig.connectTimeout,
      receiveTimeout: AppConfig.receiveTimeout,
      headers: {'Accept': 'application/json', 'Content-Type': 'application/json'},
    ));
    dio.interceptors.add(InterceptorsWrapper(
      onRequest: (options, handler) async {
        final token = await _tokenStorage.readToken();
        if (token != null && token.isNotEmpty) {
          options.headers['Authorization'] = 'Bearer $token';
        }
        handler.next(options);
      },
      onResponse: (response, handler) {
        // Newer backend routes wrap the payload: {"success": true, "data": ..., "message": ...}.
        // Unwrap it so the services see the payload whichever shape the route uses.
        final body = response.data;
        if (body is Map && body.containsKey('success') && body.containsKey('data')) {
          response.extra['envelope'] = body; // keeps "meta" (notifications paging)
          response.data = body['data'];
        }
        handler.next(response);
      },
      onError: (error, handler) async {
        if (error.response?.statusCode == 401) await _tokenStorage.clearToken();
        handler.next(error);
      },
    ));
  }

  static final ApiClient _instance = ApiClient._internal();
  factory ApiClient() => _instance;

  late final Dio dio;
  final TokenStorageService _tokenStorage = TokenStorageService();

  /// Laravel lists come back paginated: {"data": [...], "current_page": 1, ...}
  static List listFrom(dynamic data) {
    if (data is List) return data;
    if (data is Map && data['data'] is List) return data['data'] as List;
    return const [];
  }

  static const int pageSize = 50;
  static const int _maxPages = 20;

  /// GET [path] and follow the Laravel pagination, so rows after page 1 are not dropped.
  /// Page info is in `last_page` of the paginator, or in `meta.last_page` (notifications).
  Future<List> getAll(String path, {Map<String, dynamic> query = const {}}) async {
    final rows = [];
    var page = 1;
    var lastPage = 1;
    do {
      final response = await dio.get(path, queryParameters: {...query, 'per_page': pageSize, 'page': page});
      rows.addAll(listFrom(response.data));
      lastPage = _lastPage(response);
      page++;
    } while (page <= lastPage && page <= _maxPages);
    return rows;
  }

  static int _lastPage(Response response) {
    final data = response.data;
    final envelope = response.extra['envelope'];
    final raw = data is Map
        ? data['last_page']
        : envelope is Map && envelope['meta'] is Map
            ? envelope['meta']['last_page']
            : null;
    return int.tryParse('$raw') ?? 1;
  }

  /// Seconds to wait after a 429 response (Retry-After header), 60 when the header is missing
  /// (browsers hide it unless the server exposes it through CORS).
  static int retryAfterSeconds(Object error) {
    if (error is DioException) {
      final seconds = int.tryParse(error.response?.headers.value('retry-after') ?? '');
      if (seconds != null && seconds > 0) return seconds > 3600 ? 3600 : seconds;
    }
    return 60;
  }

  /// Turns any error into text you can show in a SnackBar.
  static String errorMessage(Object error) {
    if (error is DioException) {
      if (error.response?.statusCode == 429) {
        return 'Too many attempts. Please try again in ${retryAfterSeconds(error)} seconds.';
      }
      if (error.type == DioExceptionType.connectionTimeout ||
          error.type == DioExceptionType.connectionError) {
        return 'Cannot reach the server. Is the backend running?';
      }
      final data = error.response?.data;
      if (data is Map) {
        final errors = data['errors'];
        if (errors is Map && errors.isNotEmpty) {
          // 422: {"errors": {"email": ["The email has already been taken."]}}
          // 409/503: the list holds objects (stock shortages), the "message" is the readable text.
          final first = errors.values.first;
          final text = first is List && first.isNotEmpty ? first.first : first;
          if (text is String && text.isNotEmpty) return text;
        }
        final apiError = data['error'];
        if (apiError is Map && apiError['message'] != null) return '${apiError['message']}';
        if (data['message'] != null) return '${data['message']}';
      }
    }
    return 'Something went wrong. Please try again.';
  }
}
