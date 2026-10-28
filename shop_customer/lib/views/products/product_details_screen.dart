import 'package:cached_network_image/cached_network_image.dart';
import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../models/product.dart';
import '../../providers/cart_provider.dart';

class ProductDetailsScreen extends StatelessWidget {
  const ProductDetailsScreen({super.key, required this.product});
  final Product product;

  @override
  Widget build(BuildContext context) => Scaffold(
    appBar: AppBar(title: const Text('Product details')),
    body: Column(children: [
      Expanded(child: ListView(padding: const EdgeInsets.all(20), children: [
        ClipRRect(borderRadius: BorderRadius.circular(24), child: AspectRatio(aspectRatio: 1, child: CachedNetworkImage(imageUrl: product.imageUrl, fit: BoxFit.cover, errorWidget: (_, __, ___) => const ColoredBox(color: Color(0xFFECEFEA), child: Icon(Icons.shopping_bag_outlined, size: 60))))),
        const SizedBox(height: 22),
        Text(product.category.toUpperCase(), style: const TextStyle(color: Color(0xFF7C8794), fontSize: 11, fontWeight: FontWeight.w800, letterSpacing: 1.3)),
        const SizedBox(height: 8),
        Text(product.name, style: Theme.of(context).textTheme.headlineMedium),
        const SizedBox(height: 10),
        Text('\$${product.price.toStringAsFixed(2)}', style: const TextStyle(fontSize: 22, fontWeight: FontWeight.w800, color: Color(0xFF285D55))),
        const SizedBox(height: 18),
        Text(product.description, style: const TextStyle(fontSize: 15, height: 1.6)),
        const SizedBox(height: 18),
        const Row(children: [Icon(Icons.local_shipping_outlined), SizedBox(width: 10), Expanded(child: Text('Delivery options shown at checkout.'))]),
        const SizedBox(height: 10),
        const Row(children: [Icon(Icons.verified_outlined), SizedBox(width: 10), Expanded(child: Text('Product availability is confirmed by the store.'))]),
      ])),
      SafeArea(top: false, child: Padding(padding: const EdgeInsets.all(20), child: FilledButton.icon(
        onPressed: () { context.read<CartProvider>().add(product); ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Added to cart'))); },
        icon: const Icon(Icons.shopping_bag_outlined), label: const Text('Add to cart'), style: FilledButton.styleFrom(minimumSize: const Size(double.infinity, 54)),
      ))),
    ]),
  );
}
