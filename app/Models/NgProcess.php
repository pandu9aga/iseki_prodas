<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NgProcess extends Model
{
    use \Illuminate\Database\Eloquent\Factories\HasFactory;

    protected $table = 'ng_processes';

    public $timestamps = false;

    protected $fillable = [
        'app_name',
        'sequence_no',
        'current_process',
        'missing_process',
        'message',
        'created_at'
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];
}
