class AppConfig {
  AppConfig._();

  /// Android emulator: http://10.0.2.2:8000
  /// iOS simulator / Chrome / desktop: http://localhost:8000
  /// Real phone on the same Wi-Fi: http://<your laptop IP>:8000
  static const String serverUrl = 'http://localhost:8000';

  static const String apiBaseUrl = '$serverUrl/api/v1';
  static const String storageUrl = '$serverUrl/storage';

  static const Duration connectTimeout = Duration(seconds: 15);
  static const Duration receiveTimeout = Duration(seconds: 15);
}
