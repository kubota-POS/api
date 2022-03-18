<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;
    protected $table = 'orders';

    protected $fillable = [
        'item_id', 'merchant_id', 'qty', 'total_amount', 'credit', 'pay_type', 'pay_amount'
    ];

    protected $hidden = [
        'created_at', 'updated_at'
    ];
}
