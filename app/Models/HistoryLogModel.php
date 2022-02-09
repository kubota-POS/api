<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class HistoryLogModel extends Model
{
    use HasFactory;


    protected $table = 'history_log';

    protected $fillable = [
        'user_id', 'type', 'action', 'description'
    ];
}
