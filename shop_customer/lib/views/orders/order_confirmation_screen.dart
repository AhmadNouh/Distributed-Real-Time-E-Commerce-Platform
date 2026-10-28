import 'package:flutter/material.dart';
import '../../models/shop_order.dart';
import '../home/home_shell.dart';
import 'orders_screen.dart';

class OrderConfirmationScreen extends StatelessWidget {
  const OrderConfirmationScreen({super.key, required this.order});
  final ShopOrder order;

  @override
  Widget build(BuildContext context) => Scaffold(body: SafeArea(child: Padding(padding: const EdgeInsets.all(24), child: Column(mainAxisAlignment: MainAxisAlignment.center, children: [
    Container(width: 96, height: 96, decoration: const BoxDecoration(color: Color(0xFFDDEBE3), shape: BoxShape.circle), child: const Icon(Icons.check_rounded, size: 54, color: Color(0xFF285D55))),
    const SizedBox(height: 24), Text('Order placed!', style: Theme.of(context).textTheme.headlineLarge),
    const SizedBox(height: 10), const Text('Thank you. Your order has been placed.', textAlign: TextAlign.center, style: TextStyle(color: Color(0xFF7C8794))),
    const SizedBox(height: 24), Container(width: double.infinity, padding: const EdgeInsets.all(18), decoration: BoxDecoration(color: Colors.white, borderRadius: BorderRadius.circular(18)), child: Column(children: [
      const Text('ORDER NUMBER', style: TextStyle(fontSize: 11, letterSpacing: 1.2, color: Color(0xFF7C8794), fontWeight: FontWeight.w800)),
      const SizedBox(height: 8), Text(order.id, style: const TextStyle(fontSize: 20, fontWeight: FontWeight.w800)),
      const SizedBox(height: 8), Text('\$${order.total.toStringAsFixed(2)}', style: const TextStyle(color: Color(0xFF285D55), fontSize: 18, fontWeight: FontWeight.w800)),
    ])),
    const SizedBox(height: 24),
    SizedBox(width: double.infinity, child: FilledButton(onPressed: () => Navigator.pushAndRemoveUntil(context, MaterialPageRoute(builder: (_) => const OrdersScreen()), (route) => route.isFirst), child: const Text('View my orders'))),
    TextButton(onPressed: () => Navigator.pushAndRemoveUntil(context, MaterialPageRoute(builder: (_) => const HomeShell()), (_) => false), child: const Text('Continue shopping')),
  ]))));
}
