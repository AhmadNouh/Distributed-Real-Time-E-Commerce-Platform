import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../providers/auth_provider.dart';
import '../auth/login_screen.dart';
import '../home/home_shell.dart';

class SplashScreen extends StatefulWidget {
  const SplashScreen({super.key});
  @override
  State<SplashScreen> createState() => _SplashScreenState();
}

class _SplashScreenState extends State<SplashScreen> {
  @override
  void initState() {
    super.initState();
    _start();
  }

  Future<void> _start() async {
    final auth = context.read<AuthProvider>();
    final results = await Future.wait([
      auth.restoreSession(),
      Future.delayed(const Duration(milliseconds: 1200)),
    ]);
    if (!mounted) return;
    final loggedIn = results.first as bool;
    Navigator.pushReplacement(
      context,
      MaterialPageRoute(builder: (_) => loggedIn ? const HomeShell() : const LoginScreen()),
    );
  }

  @override
  Widget build(BuildContext context) => Scaffold(
    body: Container(
      width: double.infinity,
      decoration: const BoxDecoration(gradient: LinearGradient(begin: Alignment.topLeft, end: Alignment.bottomRight, colors: [Color(0xFFF7F7F3), Color(0xFFE4EEE8)])),
      child: Column(mainAxisAlignment: MainAxisAlignment.center, children: [
        Container(width: 86, height: 86, decoration: BoxDecoration(color: const Color(0xFF285D55), borderRadius: BorderRadius.circular(28)), child: const Icon(Icons.shopping_bag_outlined, color: Colors.white, size: 42)),
        const SizedBox(height: 18),
        Text('little things.', style: Theme.of(context).textTheme.headlineLarge),
        const SizedBox(height: 8),
        const Text('Thoughtful finds for everyday living'),
      ]),
    ),
  );
}
