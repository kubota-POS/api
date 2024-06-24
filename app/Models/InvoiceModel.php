<?php

namespace App\Models;

use App\Models\CreditModel;
use App\Models\CustomerModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class InvoiceModel extends Model
{
    // use HasFactory;
    use SoftDeletes;
    protected $table = 'invoice';

    protected $fillable = [
        'customer_id',
        'invoice_no',
        'pay_amount',
        'invoice_data',
        'total_amount',
        'discount',
        'credit_amount',
        'created_at'
    ];

    protected $hidden = [
        'updated_at', 'deleted_at'
    ];


    public function credit () {
        return $this->hasOne(CreditModel::class, 'invoice_id');
        // return $this->hasMany(CreditModel::class, 'invoice_id');
    }

    public function customer() {
        return $this->belongsTo(CustomerModel::class);
    }
}
