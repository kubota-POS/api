<?php

namespace App\Models;

use App\Models\InvoiceModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CreditModel extends Model
{
    use HasFactory;
    protected $table = 'credit';

    protected $fillable = [
        'invoice_id', 'credit_date', 'amount','repayment'
    ];

    protected $hidden = [
        'created_at', 'updated_at'
    ];

    public function invoice () {
        return $this->belongsTo(InvoiceModel::class);
    }

}
