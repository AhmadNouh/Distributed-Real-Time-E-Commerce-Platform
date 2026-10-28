import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../core/widgets/product_card.dart';
import '../../core/widgets/section_heading.dart';
import '../../providers/catalog_provider.dart';
import '../../providers/cart_provider.dart';
import '../products/search_screen.dart';

class HomeScreen extends StatelessWidget {
  const HomeScreen({super.key});

  @override
  Widget build(BuildContext context) {
    final catalog = context.watch<CatalogProvider>();
    final cart = context.read<CartProvider>();
    return SafeArea(child: RefreshIndicator(onRefresh: catalog.load, child: CustomScrollView(slivers: [
      SliverPadding(padding: const EdgeInsets.fromLTRB(20, 16, 20, 8), sliver: SliverToBoxAdapter(child: Row(children: [
        Expanded(child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
          const Text('YOUR EVERYDAY EDIT', style: TextStyle(color: Color(0xFF7C8794), fontSize: 11, fontWeight: FontWeight.w800, letterSpacing: 1.4)),
          const SizedBox(height: 6),
          Text('Find your little\\nfavorite things.', style: Theme.of(context).textTheme.headlineLarge),
        ])),
        IconButton.filledTonal(onPressed: () => Navigator.push(context, MaterialPageRoute(builder: (_) => const SearchScreen())), icon: const Icon(Icons.search)),
      ]))),
      SliverPadding(padding: const EdgeInsets.fromLTRB(20, 12, 20, 22), sliver: SliverToBoxAdapter(child: Container(
        padding: const EdgeInsets.all(22), decoration: BoxDecoration(color: const Color(0xFFDDEBE3), borderRadius: BorderRadius.circular(24)),
        child: Row(children: [
          Expanded(child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
            const Text('THE SLOW LIVING EDIT', style: TextStyle(fontSize: 10, fontWeight: FontWeight.w800, letterSpacing: 1.2)),
            const SizedBox(height: 9), const Text('Small details.\\nBetter everyday.', style: TextStyle(fontSize: 23, fontWeight: FontWeight.w800, height: 1.1)),
            const SizedBox(height: 14), FilledButton.tonal(onPressed: () => catalog.setCategory('Home'), child: const Text('Explore home')),
          ])),
          const Icon(Icons.spa_outlined, size: 76, color: Color(0xFF285D55)),
        ]),
      ))),
      SliverPadding(padding: const EdgeInsets.symmetric(horizontal: 20), sliver: SliverToBoxAdapter(child: const SectionHeading(title: 'Shop by category'))),
      SliverToBoxAdapter(child: SizedBox(height: 54, child: ListView(scrollDirection: Axis.horizontal, padding: const EdgeInsets.symmetric(horizontal: 20), children: [
        for (final c in catalog.categories) Padding(padding: const EdgeInsets.only(right: 8), child: ChoiceChip(label: Text(c), selected: catalog.selectedCategory == c, onSelected: (_) => catalog.setCategory(c))),
      ]))),
      SliverPadding(padding: const EdgeInsets.fromLTRB(20, 16, 20, 8), sliver: SliverToBoxAdapter(child: SectionHeading(title: 'Picked for you', action: 'Search', onTap: () => Navigator.push(context, MaterialPageRoute(builder: (_) => const SearchScreen()))))),
      if (catalog.isLoading)
        const SliverToBoxAdapter(child: Padding(padding: EdgeInsets.all(40), child: Center(child: CircularProgressIndicator())))
      else if (catalog.error != null)
        SliverToBoxAdapter(child: Padding(padding: const EdgeInsets.all(24), child: Center(child: Text(catalog.error!))))
      else if (catalog.products.isEmpty)
        const SliverToBoxAdapter(child: Padding(padding: EdgeInsets.all(24), child: Center(child: Text('No products found. Try another category.'))))
      else
        SliverPadding(padding: const EdgeInsets.fromLTRB(20, 8, 20, 24), sliver: SliverGrid(
          delegate: SliverChildBuilderDelegate((context, index) => ProductCard(product: catalog.products[index], onAdd: () {
            cart.add(catalog.products[index]);
            ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Added to cart'), duration: Duration(seconds: 1)));
          }), childCount: catalog.products.length),
          gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(crossAxisCount: 2, mainAxisSpacing: 12, crossAxisSpacing: 12, childAspectRatio: .66),
        )),
    ])));
  }
}
