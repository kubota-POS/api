<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ItemPriceModel extends Model
{
    use HasFactory;

    protected $fillable = [
        'material_code', 'price'
    ];
}
