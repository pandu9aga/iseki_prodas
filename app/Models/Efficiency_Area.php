<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Efficiency_Area extends Model
{
    protected $connection = 'efficiency';
    protected $table = 'areas';
    protected $primaryKey = 'Id_Area';
    public $timestamps = false;

    protected $fillable = ['Name_Area', 'Password_Area'];
}
