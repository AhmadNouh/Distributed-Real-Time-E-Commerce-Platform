import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../core/widgets/product_card.dart';
import '../../providers/catalog_provider.dart';
import '../../providers/cart_provider.dart';

class SearchScreen extends StatefulWidget {
  const SearchScreen({super.key});
  @override
  State<SearchScreen> createState() => _SearchScreenState();
}
class _SearchScreenState extends State<SearchScreen> {
  final _search = TextEditingController();
  @override
  void dispose() { _search.dispose(); super.dispose(); }

  @override
  Widget build(BuildContext context) {
    final catalog = context.watch<CatalogProvider>();
    final cart = context.read<CartProvider>();
    return Scaffold(appBar: AppBar(title: const Text('Discover')),
      body: Column(children: [
        Padding(padding: const EdgeInsets.fromLTRB(20, 8, 20, 12), child: TextField(
          controller: _search, onChanged: catalog.setSearch,
          decoration: const InputDecoration(hintText: 'Search products', prefixIcon: Icon(Icons.search), suffixIcon: Icon(Icons.tune)),
        )),
        SizedBox(height: 45, child: ListView(scrollDirection: Axis.horizontal, padding: const EdgeInsets.symmetric(horizontal: 20), children: [
          for (final c in catalog.categories) Padding(padding: const EdgeInsets.only(right: 8), child: ChoiceChip(label: Text(c), selected: catalog.selectedCategory == c, onSelected: (_) => catalog.setCategory(c))),
        ])),
        Expanded(child: catalog.isLoading ? const Center(child: CircularProgressIndicator()) : catalog.products.isEmpty
          ? const Center(child: Text('No matching products'))
          : GridView.builder(padding: const EdgeInsets.all(20), itemCount: catalog.products.length, gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(crossAxisCount: 2, mainAxisSpacing: 12, crossAxisSpacing: 12, childAspectRatio: .66), itemBuilder: (_, i) => ProductCard(product: catalog.products[i], onAdd: () => cart.add(catalog.products[i])))),
      ]),
    );
  }
}
