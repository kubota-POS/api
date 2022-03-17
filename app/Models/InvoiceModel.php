<?php

namespace App\Models;

use App\Models\CreditModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class InvoiceModel extends Model
{
    // use HasFactory;
    use SoftDeletes;
    protected $table = 'invoice';

    protected $fillable = [
       'invoice_no',
       'pay_amount',
       'customer_id',
       'invoice_data',
       'total_amount',
       'discount',
       'cash_back',
       'created_at'
    ];

    protected $hidden = [
        'created_at', 'updated_at', 'deleted_at'
    ];


    public function credit () {
        return $this->hasOne(CreditModel::class, 'invoice_id');
    }
}
