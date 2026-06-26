<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WaRangkumanHistory extends Model
{
    protected $table = 'wa_rangkuman_history';
    protected $primaryKey = 'Id_History';

    public $timestamps = false;

    protected $fillable = [
        'Log_Date',
        'Category_Group',
        'Category_Item',
        'Target',
        'Actual',
        'Selisih',
        'Grand_Total',
        'Koreksi',
        'Created_At',
    ];
}
