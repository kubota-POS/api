<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class InvoiceModel extends Model
{
    // use HasFactory;
    use SoftDeletes;
    protected $table = 'invoice';

    protected $fillable = [
       'invoice_id',
       'customer_id',
       'invoice_data',
       'total_amount',
       'discount',
       'cash_back'
    ];
}
