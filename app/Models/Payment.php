<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = ['order_id', 'method', 'status', 'amount', 'transaction_id', 'gateway_response', 'paid_at'];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
