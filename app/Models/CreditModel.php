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
        'invoice_id', 'invoice_no', 'amount','repayment'
    ];

    protected $hidden = [
        'created_at', 'updated_at', 'deleted_at'
    ];

    public function invoice () {
        return $this->belongsTo(InvoiceModel::class);
    }

}
