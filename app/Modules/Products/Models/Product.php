<?php
namespace App\Modules\Products\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Product extends Model
{
    protected $fillable = ['name','description','price','category_id','image'];

    protected $appends = ['image_url'];
    
    public function getImageUrlAttribute(): string
    {
        return $this->image ? asset($this->image) : '';
    }

}
