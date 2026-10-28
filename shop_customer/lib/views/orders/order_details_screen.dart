import 'package:flutter/material.dart';
import 'package:intl/intl.dart';
import '../../models/shop_order.dart';

class OrderDetailsScreen extends StatelessWidget {
  const OrderDetailsScreen({super.key, required this.order});
  final ShopOrder order;

  @override
  Widget build(BuildContext context) => Scaffold(appBar: AppBar(title: const Text('Order details')), body: ListView(padding: const EdgeInsets.all(20), children: [
    Container(padding: const EdgeInsets.all(20), decoration: BoxDecoration(color: Colors.white, borderRadius: BorderRadius.circular(20)), child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
      const Text('ORDER NUMBER', style: TextStyle(fontSize: 11, letterSpacing: 1.2, color: Color(0xFF7C8794), fontWeight: FontWeight.w800)),
      const SizedBox(height: 8), Text(order.id, style: Theme.of(context).textTheme.headlineMedium),
      const SizedBox(height: 14), Text('Placed ${DateFormat('d MMMM yyyy, h:mm a').format(order.createdAt)}'),
      const SizedBox(height: 14), Row(children: [const Expanded(child: Text('Status')), Text(order.status, style: const TextStyle(color: Color(0xFF285D55), fontWeight: FontWeight.w800))]),
      const Divider(height: 28), const Text('Items', style: TextStyle(fontWeight: FontWeight.w800)),
      const SizedBox(height: 8), Text(order.itemsSummary.isEmpty ? 'Order items will be returned by the API.' : order.itemsSummary),
      const Divider(height: 28), Row(children: [const Expanded(child: Text('Total', style: TextStyle(fontWeight: FontWeight.w800))), Text('\$${order.total.toStringAsFixed(2)}', style: const TextStyle(fontSize: 20, fontWeight: FontWeight.w800))]),
    ])),
    const SizedBox(height: 16),
    const Text('Order status updates will be synced from the backend after API integration.', style: TextStyle(color: Color(0xFF7C8794))),
  ]));
}
