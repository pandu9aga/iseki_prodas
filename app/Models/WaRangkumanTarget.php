<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WaRangkumanTarget extends Model
{
    protected $table = 'wa_rangkuman_targets';
    protected $primaryKey = 'Id_Target';

    public $timestamps = false;

    protected $fillable = [
        'Target_Date',
        'Category_Group',
        'Category_Item',
        'Target',
        'Created_At',
        'Updated_At',
    ];
}
