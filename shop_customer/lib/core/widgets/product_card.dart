import 'package:cached_network_image/cached_network_image.dart';
import 'package:flutter/material.dart';
import '../../models/product.dart';
import '../../views/products/product_details_screen.dart';

class ProductCard extends StatelessWidget {
  const ProductCard({super.key, required this.product, required this.onAdd});
  final Product product;
  final VoidCallback onAdd;

  @override
  Widget build(BuildContext context) => InkWell(
    borderRadius: BorderRadius.circular(18),
    onTap: () => Navigator.push(context, MaterialPageRoute(builder: (_) => ProductDetailsScreen(product: product))),
    child: Container(
      decoration: BoxDecoration(color: Colors.white, borderRadius: BorderRadius.circular(18)),
      padding: const EdgeInsets.all(10),
      child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
        Expanded(child: Stack(children: [
          ClipRRect(
            borderRadius: BorderRadius.circular(14),
            child: SizedBox(width: double.infinity, child: CachedNetworkImage(
              imageUrl: product.imageUrl, fit: BoxFit.cover,
              placeholder: (_, __) => Container(color: const Color(0xFFECEFEA), child: const Icon(Icons.image_outlined, size: 34)),
              errorWidget: (_, __, ___) => Container(color: const Color(0xFFECEFEA), child: const Icon(Icons.shopping_bag_outlined, size: 34)),
            )),
          ),
          if (product.badge != null)
            Positioned(left: 7, top: 7, child: Container(
              padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 5),
              decoration: BoxDecoration(color: const Color(0xFFE7B66A), borderRadius: BorderRadius.circular(8)),
              child: Text(product.badge!, style: const TextStyle(fontSize: 10, fontWeight: FontWeight.w800)),
            )),
        ])),
        const SizedBox(height: 10),
        Text(product.name, maxLines: 1, overflow: TextOverflow.ellipsis, style: const TextStyle(fontWeight: FontWeight.w700)),
        const SizedBox(height: 4),
        Row(children: [
          Expanded(child: Text('\$${product.price.toStringAsFixed(2)}', style: const TextStyle(fontWeight: FontWeight.w800, fontSize: 16))),
          InkWell(
            onTap: onAdd,
            borderRadius: BorderRadius.circular(10),
            child: Container(padding: const EdgeInsets.all(7), decoration: BoxDecoration(color: const Color(0xFF285D55), borderRadius: BorderRadius.circular(10)), child: const Icon(Icons.add, color: Colors.white, size: 18)),
          ),
        ]),
      ]),
    ),
  );
}
