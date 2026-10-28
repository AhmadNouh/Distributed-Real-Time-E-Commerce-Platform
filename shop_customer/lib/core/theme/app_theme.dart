import 'package:flutter/material.dart';

class AppTheme {
  static const ink = Color(0xFF17212B);
  static const muted = Color(0xFF7C8794);
  static const primary = Color(0xFF285D55);
  static const accent = Color(0xFFE7B66A);
  static const canvas = Color(0xFFF7F7F3);

  static ThemeData get light => ThemeData(
    useMaterial3: true,
    scaffoldBackgroundColor: canvas,
    colorScheme: ColorScheme.fromSeed(seedColor: primary, surface: Colors.white),
    appBarTheme: const AppBarTheme(
      backgroundColor: canvas, foregroundColor: ink, elevation: 0, centerTitle: false,
    ),
    textTheme: const TextTheme(
      headlineLarge: TextStyle(fontSize: 30, fontWeight: FontWeight.w800, color: ink, height: 1.1),
      headlineMedium: TextStyle(fontSize: 23, fontWeight: FontWeight.w800, color: ink),
      titleLarge: TextStyle(fontSize: 18, fontWeight: FontWeight.w700, color: ink),
      bodyMedium: TextStyle(fontSize: 14, color: ink, height: 1.4),
      bodySmall: TextStyle(fontSize: 12, color: muted),
    ),
    inputDecorationTheme: InputDecorationTheme(
      filled: true, fillColor: Colors.white,
      contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 15),
      border: OutlineInputBorder(borderRadius: BorderRadius.circular(14), borderSide: BorderSide.none),
      enabledBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(14), borderSide: const BorderSide(color: Color(0xFFE7E9E5))),
      focusedBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(14), borderSide: const BorderSide(color: primary, width: 1.4)),
    ),
    filledButtonTheme: FilledButtonThemeData(
      style: FilledButton.styleFrom(
        backgroundColor: primary, foregroundColor: Colors.white, minimumSize: const Size(0, 52),
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(15)),
        textStyle: const TextStyle(fontWeight: FontWeight.w700, fontSize: 15),
      ),
    ),
    cardTheme: CardThemeData(color: Colors.white, elevation: 0, shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20))),
  );
}
