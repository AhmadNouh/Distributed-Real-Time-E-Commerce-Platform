<?php
namespace App\Modules\Orders\Models;
use Illuminate\Database\Eloquent\Model;
class Order extends Model
{
    protected $fillable = ['user_id','status','total','uuid'];
    public function items() { return $this->hasMany(OrderItem::class); }
    public function user() { return $this->belongsTo(\App\Modules\Users\Models\User::class); }
}
