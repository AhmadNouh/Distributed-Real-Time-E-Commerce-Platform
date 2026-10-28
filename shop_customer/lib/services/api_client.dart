import 'package:dio/dio.dart';
import '../core/config/app_config.dart';
import 'token_storage_service.dart';

class ApiClient {
  ApiClient({TokenStorageService? tokenStorage}) : _tokenStorage = tokenStorage ?? TokenStorageService() {
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
    ));
  }

  late final Dio dio;
  final TokenStorageService _tokenStorage;
}
