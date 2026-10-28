class AppConfig {
  AppConfig._();

  /// Keep true until your backend team gives you working APIs and schemas.
  static const bool useMockData = true;

  /// Android emulator: often http://10.0.2.2:8000
  /// Physical phone: use your computer's LAN IP, e.g. http://192.168.1.20:8000
  static const String apiBaseUrl = 'http://10.0.2.2:8000';
  static const Duration connectTimeout = Duration(seconds: 15);
  static const Duration receiveTimeout = Duration(seconds: 15);
}
