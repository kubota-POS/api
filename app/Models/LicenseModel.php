<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LicenseModel extends Model
{
    use HasFactory;

    protected $table = 'license';

    protected $fillable = [
        'license', 'first_name', 'last_name', 'display_name', 'phone', 'email', 'address'
    ];


    public function checkLicense() {

    }


}
