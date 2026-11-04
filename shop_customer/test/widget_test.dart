import 'package:flutter_test/flutter_test.dart';
import 'package:shop_customer/models/category.dart';
import 'package:shop_customer/models/product.dart';
import 'package:shop_customer/models/shop_order.dart';
import 'package:shop_customer/models/user_profile.dart';

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
    // The list does not send any stock: do not invent one.
    expect(product.availableQuantity, isNull);
    expect(product.isOutOfStock, isFalse);
  });

  test('Product.fromJson reads the warehouse stock of GET /products/{id}', () {
    final inStock = Product.fromJson({'id': 1, 'name': 'A', 'price': '1.00', 'available_quantity': 4, 'stock_status': 'available'});
    expect(inStock.availableQuantity, 4);
    final soldOut = Product.fromJson({'id': 1, 'name': 'A', 'price': '1.00', 'available_quantity': 0});
    expect(soldOut.isOutOfStock, isTrue);
    final warehouseDown = Product.fromJson({'id': 1, 'name': 'A', 'price': '1.00', 'available_quantity': null, 'stock_status': 'unavailable'});
    expect(warehouseDown.isOutOfStock, isFalse);
  });

  test('ShopOrder.fromJson reads the order the API returns', () {
    final order = ShopOrder.fromJson(
      {
        'id': 9,
        'uuid': 'u',
        'status': 'pending',
        'total': '1275.00',
        'created_at': '2026-11-04T10:00:00.000000Z',
        'items': [
          {'id': 1, 'order_id': 9, 'product_id': 2, 'quantity': 1, 'price': '75.00'},
        ],
      },
      productNames: {'2': 'Mechanical Keyboard'},
    );
    expect(order.status, 'Pending');
    expect(order.total, 1275.0);
    expect(order.itemsSummary, 'Mechanical Keyboard x1');
  });

  test('UserProfile.fromJson tolerates the user shapes the API and web app use', () {
    expect(UserProfile.fromJson({'name': 'A', 'email': 'a@x.com'}).role, 'customer');
    expect(UserProfile.fromJson({'name': 'A', 'email': 'a@x.com', 'role': 'admin'}).role, 'admin');
    expect(UserProfile.fromJson({'name': 'A', 'email': 'a@x.com', 'roles': ['admin']}).role, 'admin');
  });

  test('ProductCategory.fromJson reads id and name', () {
    final category = ProductCategory.fromJson({'id': 1, 'name': 'Electronics'});
    expect(category.id, 1);
    expect(category.name, 'Electronics');
  });
}
