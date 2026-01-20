<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

// app/Models/Tractor.php
class Efficiency_Tractor extends Model
{
    protected $connection = 'efficiency';
    protected $table = 'tractors';
    protected $primaryKey = 'Id_Tractor'; // ✅ harus sesuai
    public $timestamps = false;

    protected $fillable = [
        'Name_Tractor',
        'Group_Tractor',
        'Hour_Tractor',
        'Id_Area'
    ];
}
