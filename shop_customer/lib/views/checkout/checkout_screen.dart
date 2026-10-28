import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../core/widgets/app_button.dart';
import '../../providers/cart_provider.dart';
import '../../providers/orders_provider.dart';
import '../orders/order_confirmation_screen.dart';

class CheckoutScreen extends StatefulWidget {
  const CheckoutScreen({super.key});
  @override
  State<CheckoutScreen> createState() => _CheckoutScreenState();
}
class _CheckoutScreenState extends State<CheckoutScreen> {
  final _form = GlobalKey<FormState>();
  final _name = TextEditingController();
  final _phone = TextEditingController();
  final _address = TextEditingController();
  bool _placing = false;

  @override
  void dispose() { _name.dispose(); _phone.dispose(); _address.dispose(); super.dispose(); }

  Future<void> _placeOrder() async {
    if (!_form.currentState!.validate()) return;
    final cart = context.read<CartProvider>();
    setState(() => _placing = true);
    try {
      final summary = cart.items.map((i) => '${i.product.name} x${i.quantity}').join(', ');
      final order = await context.read<OrdersProvider>().checkout(total: cart.total, summary: summary);
      cart.clear();
      if (!mounted) return;
      Navigator.pushReplacement(context, MaterialPageRoute(builder: (_) => OrderConfirmationScreen(order: order)));
    } catch (_) {
      if (mounted) ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Could not place order. Please try again.')));
    } finally { if (mounted) setState(() => _placing = false); }
  }

  @override
  Widget build(BuildContext context) {
    final cart = context.watch<CartProvider>();
    return Scaffold(appBar: AppBar(title: const Text('Checkout')), body: SafeArea(child: Form(key: _form, child: ListView(padding: const EdgeInsets.all(20), children: [
      Text('Delivery details', style: Theme.of(context).textTheme.headlineMedium),
      const SizedBox(height: 18),
      TextFormField(controller: _name, decoration: const InputDecoration(labelText: 'Full name', prefixIcon: Icon(Icons.person_outline)), validator: (v) => v == null || v.trim().isEmpty ? 'Enter your name' : null),
      const SizedBox(height: 14),
      TextFormField(controller: _phone, keyboardType: TextInputType.phone, decoration: const InputDecoration(labelText: 'Phone number', prefixIcon: Icon(Icons.phone_outlined)), validator: (v) => v == null || v.trim().length < 6 ? 'Enter a valid phone number' : null),
      const SizedBox(height: 14),
      TextFormField(controller: _address, maxLines: 3, decoration: const InputDecoration(labelText: 'Delivery address', prefixIcon: Icon(Icons.location_on_outlined)), validator: (v) => v == null || v.trim().length < 5 ? 'Enter a complete address' : null),
      const SizedBox(height: 24),
      Text('Order summary', style: Theme.of(context).textTheme.titleLarge),
      const SizedBox(height: 12),
      for (final item in cart.items) Padding(padding: const EdgeInsets.only(bottom: 8), child: Row(children: [Expanded(child: Text('${item.product.name} × ${item.quantity}')), Text('\$${item.total.toStringAsFixed(2)}')])),
      const Divider(height: 24),
      Row(children: [const Expanded(child: Text('Total', style: TextStyle(fontWeight: FontWeight.w800))), Text('\$${cart.total.toStringAsFixed(2)}', style: const TextStyle(fontSize: 20, fontWeight: FontWeight.w800))]),
      const SizedBox(height: 22),
      AppButton(label: 'Place order', loading: _placing, onPressed: cart.items.isEmpty ? null : _placeOrder),
      const SizedBox(height: 10),
      const Text('Demo checkout: payment processing is not connected.', textAlign: TextAlign.center, style: TextStyle(color: Color(0xFF7C8794), fontSize: 12)),
    ]))));
  }
}
