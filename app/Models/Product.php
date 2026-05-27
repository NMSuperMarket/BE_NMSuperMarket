<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'category_id', 'name', 'slug', 'sku', 'description', 
        'price', 'sale_price', 'unit', 'thumbnail', 'brand', 
        'origin', 'weight', 'is_active', 'is_featured', 'sold_count'
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function images()
    {
        return $this->hasMany(ProductImage::class);
    }

    public function inventory()
    {
        return $this->hasOne(Inventory::class);
    }
}
