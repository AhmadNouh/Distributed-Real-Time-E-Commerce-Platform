<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use App\Modules\Users\Models\User;
use App\Modules\Products\Models\Category;
use App\Modules\Products\Models\Product;
use App\Modules\Orders\Models\Order;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        $admin = User::create(['name'=>'Admin','email'=>'admin@store.test','password'=>bcrypt('password123'),'role'=>'admin']);
        User::create(['name'=>'Alice','email'=>'alice@test.com','password'=>bcrypt('password123'),'role'=>'customer']);
        User::create(['name'=>'Bob','email'=>'bob@test.com','password'=>bcrypt('password123'),'role'=>'customer']);
        $cat = Category::create(['name'=>'Electronics']);
        $cat2 = Category::create(['name'=>'Books']);
        $cat3 = Category::create(['name'=>'Clothing']);
        for($i=1;$i<=8;$i++) {
            Product::create([
                'name'=>'Product '.$i,
                'description'=>'Desc '.$i,
                'price'=>($i*10)+9.99,
                'category_id'=>[$cat->id,$cat2->id,$cat3->id][($i-1)%3],
                'image'=>'products/placeholder.jpg',
            ]);
        }
        Order::create(['user_id'=>$admin->id,'status'=>'pending','total'=>199.98,'uuid'=>'order-001']);
    }
}
