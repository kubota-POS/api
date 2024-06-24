<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\CategoryModel;

class ItemModel extends Model
{
    use HasFactory;

    protected $table = 'items';

    protected $fillable = [
        'code', 'category_id', 'eng_name', 'mm_name', 'model', 'qty', 'price', 'percentage', 'fix_amount', 'location', 'active'
    ];

    protected $hidden = [
        'created_at', 'updated_at', 'category_id'
    ];

    protected $casts = [
        'active' => 'boolean'
    ];

    public function category() {
        return $this->belongsTo(CategoryModel::class);
    }
}
