<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BackupModel extends Model
{
    use HasFactory;

    protected $table = 'item';
    protected $connection = 'backup';
}
