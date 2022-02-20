<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NumberSpecificationModel extends Model
{
    use HasFactory;

    protected $table = 'number_specification';

    protected $fillable = [
        'set_number', 'set_char', 'active'
    ];

    protected $casts = [
        'active' => 'boolean'
    ];

    protected $hidden = [
        'created_at', 'updated_at'
    ];
}
