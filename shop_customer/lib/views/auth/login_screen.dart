import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../core/widgets/app_button.dart';
import '../../providers/auth_provider.dart';
import '../home/home_shell.dart';
import 'register_screen.dart';

class LoginScreen extends StatefulWidget {
  const LoginScreen({super.key});
  @override
  State<LoginScreen> createState() => _LoginScreenState();
}
class _LoginScreenState extends State<LoginScreen> {
  final _form = GlobalKey<FormState>();
  final _email = TextEditingController();
  final _password = TextEditingController();
  bool _obscure = true;

  @override
  void dispose() { _email.dispose(); _password.dispose(); super.dispose(); }

  Future<void> _login() async {
    if (!_form.currentState!.validate()) return;
    final ok = await context.read<AuthProvider>().login(_email.text.trim(), _password.text);
    if (!mounted) return;
    if (ok) Navigator.pushAndRemoveUntil(context, MaterialPageRoute(builder: (_) => const HomeShell()), (_) => false);
    else ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Login failed. Please try again.')));
  }

  @override
  Widget build(BuildContext context) {
    final auth = context.watch<AuthProvider>();
    return Scaffold(body: SafeArea(child: SingleChildScrollView(padding: const EdgeInsets.all(24), child: Form(key: _form, child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
      const SizedBox(height: 32),
      Container(width: 60, height: 60, decoration: BoxDecoration(color: const Color(0xFFDDEBE3), borderRadius: BorderRadius.circular(20)), child: const Icon(Icons.shopping_bag_outlined, color: Color(0xFF285D55), size: 30)),
      const SizedBox(height: 28),
      Text('Welcome\\nback.', style: Theme.of(context).textTheme.headlineLarge),
      const SizedBox(height: 10),
      const Text('Sign in to find your next favorite thing.', style: TextStyle(color: Color(0xFF7C8794))),
      const SizedBox(height: 30),
      const Text('Email address'),
      const SizedBox(height: 8),
      TextFormField(controller: _email, keyboardType: TextInputType.emailAddress, decoration: const InputDecoration(hintText: 'you@example.com', prefixIcon: Icon(Icons.mail_outline)), validator: (v) => v == null || !v.contains('@') ? 'Enter a valid email address' : null),
      const SizedBox(height: 18),
      const Text('Password'),
      const SizedBox(height: 8),
      TextFormField(controller: _password, obscureText: _obscure, decoration: InputDecoration(hintText: 'Enter your password', prefixIcon: const Icon(Icons.lock_outline), suffixIcon: IconButton(icon: Icon(_obscure ? Icons.visibility_outlined : Icons.visibility_off_outlined), onPressed: () => setState(() => _obscure = !_obscure))), validator: (v) => v == null || v.length < 8 ? 'Password must be at least 8 characters' : null),
      const SizedBox(height: 24),
      AppButton(label: 'Sign in', loading: auth.isLoading, onPressed: _login),
      const SizedBox(height: 12),
      Center(child: TextButton(onPressed: () => Navigator.push(context, MaterialPageRoute(builder: (_) => const RegisterScreen())), child: const Text("New here? Create an account"))),
      const SizedBox(height: 18),
      if (auth.error != null) Text(auth.error!, style: const TextStyle(color: Colors.red)),
      if (true) const Center(child: Text('Demo mode: use any valid email and an 8+ character password.', textAlign: TextAlign.center, style: TextStyle(color: Color(0xFF7C8794), fontSize: 12))),
    ])))));
  }
}
