<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'price',
        'stock',
        'image',
        'is_active'
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'stock' => 'integer',
        'is_active' => 'boolean'
    ];

    protected $attributes = [
        'is_active' => true
    ];

    protected $appends = ['image_url'];

    // Image URL accessor
    public function getImageUrlAttribute()
    {
        return $this->image ? asset('storage/products/' . $this->image) : null;
    }

    // Check if product has enough stock
    public function hasStock($quantity)
    {
        return $this->stock >= $quantity;
    }

    // Decrement stock
    public function decrementStock($quantity)
    {
        if ($this->hasStock($quantity)) {
            $this->decrement('stock', $quantity);
            return true;
        }
        return false;
    }

    // Increment stock
    public function incrementStock($quantity)
    {
        $this->increment('stock', $quantity);
        return true;
    }

    // Get low stock products
    public static function getLowStockProducts($threshold = 10)
    {
        return self::where('stock', '<=', $threshold)
            ->where('is_active', true)
            ->get();
    }

    // Get out of stock products
    public static function getOutOfStockProducts()
    {
        return self::where('stock', 0)
            ->where('is_active', true)
            ->get();
    }

    // Add accessor for status
    public function getStatusAttribute()
    {
        return $this->is_active ? 'active' : 'inactive';
    }
}
