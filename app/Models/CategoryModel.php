<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\ItemModel;

class CategoryModel extends Model
{
    use HasFactory;

    protected $table = 'category';

    protected $fillable = [
        'name', 'description'
    ];

    protected $hidden = [
        'created_at', 'updated_at'
    ];
}
