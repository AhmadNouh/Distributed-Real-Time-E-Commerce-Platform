import 'package:flutter_test/flutter_test.dart';
import 'package:shop_customer/models/category.dart';
import 'package:shop_customer/models/product.dart';

void main() {
  test('Product.fromJson reads the API product shape', () {
    final product = Product.fromJson(
      {
        'id': 3,
        'name': 'Product 3',
        'description': 'Desc',
        'price': '29.99',
        'category_id': 2,
        'image': 'products/x.jpg',
      },
      categoryNames: {2: 'Books'},
    );
    expect(product.id, '3');
    expect(product.price, 29.99);
    expect(product.category, 'Books');
    expect(product.imageUrl, endsWith('/storage/products/x.jpg'));
  });

  test('ProductCategory.fromJson reads id and name', () {
    final category = ProductCategory.fromJson({'id': 1, 'name': 'Electronics'});
    expect(category.id, 1);
    expect(category.name, 'Electronics');
  });
}
