<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up() {
        Schema::create('orders', function(Blueprint $t){
            $t->id(); $t->foreignId('user_id')->constrained('users');
            $t->string('status')->default('pending');
            $t->decimal('total', 10, 2)->default(0);
            $t->string('uuid')->unique();
            $t->timestamps();
        });
        Schema::create('order_items', function(Blueprint $t){
            $t->id(); $t->foreignId('order_id')->constrained('orders')->onDelete('cascade');
            $t->foreignId('product_id')->constrained('products');
            $t->integer('quantity'); $t->decimal('price',10,2);
            $t->timestamps();
        });
    }
    public function down() {
        Schema::dropIfExists('order_items'); Schema::dropIfExists('orders');
    }
};
