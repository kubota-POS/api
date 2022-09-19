<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\InvoiceModel;
use App\Models\CreditModel;

class CustomerModel extends Model
{
    use HasFactory;
    
    protected $table = 'customers';

    protected $fillable = [
        'name', 'email', 'phone', 'address'
    ];

    protected $hidden = [
        'created_at', 'updated_at',
    ];

    public function invoice() {
        return $this->hasMany(InvoiceModel::class, 'customer_id');
    }

    public function credit() {
        return $this->hasMany(CreditModel::class, 'customer_id');
    }
}
