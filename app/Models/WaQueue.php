<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WaQueue extends Model
{
    protected $connection = 'scan';

    protected $table = 'wa_queues';

    protected $fillable = [
        'message',
        'group_id',
        'status', //sent,pending (default)
    ];
}
