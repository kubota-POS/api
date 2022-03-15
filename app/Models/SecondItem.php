<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SecondItem extends Model
{
    use HasFactory;
    protected $connection = 'mysql2';
    protected $table = 'second_items';

    protected $fillable = [
        'm_code','m_name','m_photo','m_qty','price_code','sell_percentage','location','c_date','m_active'
    ];

    protected $hidden = [
        'created_at', 'updated_at'
    ];
}
