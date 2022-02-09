<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeviceModel extends Model
{
    use HasFactory;

    protected $table = 'device';

    protected $fillable = [
        'name', 'ip', 'mac', 'note', 'active'
    ];

    protected $hidden = [
        'created_at', 'updated_at',
    ];

    protected $casts = [
        'active' => 'boolean'
    ];
}
