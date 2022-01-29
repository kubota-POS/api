<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use \Carbon\Carbon;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Crypt;

class LicenseModel extends Model
{
    use HasFactory;

    protected $table = 'license';

    protected $fillable = [
        'serial', 'token'
    ];

}
