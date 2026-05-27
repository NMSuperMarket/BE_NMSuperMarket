<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'order_code', 'user_id', 'status', 'subtotal', 'shipping_fee', 
        'discount', 'total', 'payment_method', 'payment_status', 
        'shipping_name', 'shipping_phone', 'shipping_address', 'shipping_ward', 
        'shipping_district', 'shipping_province', 'shipper_id', 'shipping_provider', 
        'tracking_code', 'confirmed_at', 'shipped_at', 'delivered_at', 
        'cancelled_at', 'cancel_reason', 'note'
    ];

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function invoice()
    {
        return $this->hasOne(Invoice::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
