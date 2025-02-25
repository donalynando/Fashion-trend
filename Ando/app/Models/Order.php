<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'order_number',
        'status',
        'shipping_address',
        'shipping_option',
        'shipping_fee',
        'payment_method',
        'payment_fee',
        'subtotal',
        'voucher_code',
        'voucher_discount',
        'total'
    ];

    protected $casts = [
        'shipping_address' => 'array',
        'shipping_fee' => 'float',
        'payment_fee' => 'float',
        'subtotal' => 'float',
        'voucher_discount' => 'float',
        'total' => 'float'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    // Alias for items() to maintain backward compatibility
    public function orderItems()
    {
        return $this->items();
    }
}
