import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../providers/auth_provider.dart';
import '../auth/login_screen.dart';
import '../orders/orders_screen.dart';

class ProfileScreen extends StatelessWidget {
  const ProfileScreen({super.key});

  @override
  Widget build(BuildContext context) {
    final auth = context.watch<AuthProvider>();
    final user = auth.user;
    return Scaffold(appBar: AppBar(title: const Text('Your profile')), body: ListView(padding: const EdgeInsets.all(20), children: [
      Container(padding: const EdgeInsets.all(20), decoration: BoxDecoration(color: const Color(0xFFDDEBE3), borderRadius: BorderRadius.circular(22)), child: Row(children: [
        const CircleAvatar(radius: 28, backgroundColor: Color(0xFF285D55), child: Icon(Icons.person_outline, color: Colors.white, size: 30)),
        const SizedBox(width: 14), Expanded(child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
          Text(user?.name ?? 'Guest customer', style: const TextStyle(fontWeight: FontWeight.w800, fontSize: 18)),
          const SizedBox(height: 4), Text(user?.email ?? 'Sign in to your account'),
        ])),
      ])),
      const SizedBox(height: 22),
      _ProfileTile(icon: Icons.receipt_long_outlined, title: 'My orders', subtitle: 'View your order history', onTap: () => Navigator.push(context, MaterialPageRoute(builder: (_) => const OrdersScreen()))),
      _ProfileTile(icon: Icons.location_on_outlined, title: 'Delivery addresses', subtitle: 'Address book will be connected later', onTap: () => _message(context, 'Address management is not connected yet.')),
      _ProfileTile(icon: Icons.help_outline, title: 'Help & support', subtitle: 'Questions about your order?', onTap: () => _message(context, 'Support details can be added by the team.')),
      _ProfileTile(icon: Icons.info_outline, title: 'About this app', subtitle: 'Customer shopping experience', onTap: () => showAboutDialog(context: context, applicationName: 'little things.', applicationVersion: '1.0.0')),
      const SizedBox(height: 16),
      OutlinedButton.icon(onPressed: () async {
        await context.read<AuthProvider>().logout();
        if (context.mounted) Navigator.pushAndRemoveUntil(context, MaterialPageRoute(builder: (_) => const LoginScreen()), (_) => false);
      }, icon: const Icon(Icons.logout), label: const Text('Sign out'), style: OutlinedButton.styleFrom(minimumSize: const Size(double.infinity, 50))),
    ]));
  }

  void _message(BuildContext context, String message) => ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(message)));
}
class _ProfileTile extends StatelessWidget {
  const _ProfileTile({required this.icon, required this.title, required this.subtitle, required this.onTap});
  final IconData icon; final String title; final String subtitle; final VoidCallback onTap;
  @override
  Widget build(BuildContext context) => ListTile(
    contentPadding: const EdgeInsets.symmetric(vertical: 5, horizontal: 4),
    leading: Container(padding: const EdgeInsets.all(10), decoration: BoxDecoration(color: Colors.white, borderRadius: BorderRadius.circular(12)), child: Icon(icon)),
    title: Text(title, style: const TextStyle(fontWeight: FontWeight.w700)), subtitle: Text(subtitle),
    trailing: const Icon(Icons.chevron_right), onTap: onTap,
  );
}
