<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up() { Schema::create('products', function(Blueprint $t){ $t->id(); $t->string('name'); $t->text('description')->nullable(); $t->decimal('price',10,2); $t->foreignId('category_id')->constrained('categories'); $t->string('image')->nullable(); $t->timestamps(); }); }
    public function down() { Schema::dropIfExists('products'); }
};
