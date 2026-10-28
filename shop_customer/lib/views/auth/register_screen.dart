import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../core/widgets/app_button.dart';
import '../../providers/auth_provider.dart';
import '../home/home_shell.dart';

class RegisterScreen extends StatefulWidget {
  const RegisterScreen({super.key});
  @override
  State<RegisterScreen> createState() => _RegisterScreenState();
}
class _RegisterScreenState extends State<RegisterScreen> {
  final _form = GlobalKey<FormState>();
  final _name = TextEditingController();
  final _email = TextEditingController();
  final _password = TextEditingController();

  @override
  void dispose() { _name.dispose(); _email.dispose(); _password.dispose(); super.dispose(); }

  Future<void> _register() async {
    if (!_form.currentState!.validate()) return;
    final ok = await context.read<AuthProvider>().register(_name.text.trim(), _email.text.trim(), _password.text);
    if (!mounted) return;
    if (ok) Navigator.pushAndRemoveUntil(context, MaterialPageRoute(builder: (_) => const HomeShell()), (_) => false);
    else ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(context.read<AuthProvider>().error ?? 'Could not create account. Please try again.')));
  }

  @override
  Widget build(BuildContext context) {
    final auth = context.watch<AuthProvider>();
    return Scaffold(appBar: AppBar(), body: SafeArea(child: SingleChildScrollView(padding: const EdgeInsets.fromLTRB(24, 8, 24, 24), child: Form(key: _form, child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
      Text('Create account', style: Theme.of(context).textTheme.headlineLarge),
      const SizedBox(height: 8),
      const Text('A few details and you are all set.', style: TextStyle(color: Color(0xFF7C8794))),
      const SizedBox(height: 28),
      const Text('Full name'), const SizedBox(height: 8),
      TextFormField(controller: _name, decoration: const InputDecoration(hintText: 'Your name', prefixIcon: Icon(Icons.person_outline)), validator: (v) => v == null || v.trim().length < 2 ? 'Enter your name' : null),
      const SizedBox(height: 16),
      const Text('Email address'), const SizedBox(height: 8),
      TextFormField(controller: _email, keyboardType: TextInputType.emailAddress, decoration: const InputDecoration(hintText: 'you@example.com', prefixIcon: Icon(Icons.mail_outline)), validator: (v) => v == null || !v.contains('@') ? 'Enter a valid email address' : null),
      const SizedBox(height: 16),
      const Text('Password'), const SizedBox(height: 8),
      TextFormField(controller: _password, obscureText: true, decoration: const InputDecoration(hintText: 'At least 8 characters', prefixIcon: Icon(Icons.lock_outline)), validator: (v) => v == null || v.length < 8 ? 'Use at least 8 characters' : null),
      const SizedBox(height: 24),
      AppButton(label: 'Create account', loading: auth.isLoading, onPressed: _register),
    ])))));
  }
}
