<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Merchant extends Model
{
    use HasFactory;
    protected $table = 'merchants';

    protected $fillable = [
        'name', 'email', 'address', 'phone'
    ];

    protected $hidden = [
        'created_at', 'updated_at'
    ];

}
