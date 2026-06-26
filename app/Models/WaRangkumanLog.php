<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WaRangkumanLog extends Model
{
    protected $table = 'wa_rangkuman_logs';
    protected $primaryKey = 'Id_Log';

    public $timestamps = false;

    protected $fillable = [
        'Action_Type',
        'File_Name',
        'Total_Rows',
        'Month',
        'Created_By',
        'Created_At',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'Created_By', 'Id_User');
    }
}
