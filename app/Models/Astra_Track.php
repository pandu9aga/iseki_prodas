<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Astra_Track extends Model
{
    use HasFactory;

    protected $connection = 'astra';
    protected $table = 'tracks'; // Nama tabel
    protected $primaryKey = 'Id_Track'; // Nama primary key

    public $timestamps = false; // Jika tabel tidak memiliki created_at dan updated_at

    protected $fillable = [
        'Id_User',
        'Id_Type',
        'Id_Area',
        'Time_Track',
        'Status_Track'
    ];

    public function area()
    {
        return $this->belongsTo(Astra_Area::class, 'Id_Area', 'Id_Area');
    }

    public function track_photo()
    {
        return $this->hasMany(Astra_Track_Photo::class, 'Id_Track', 'Id_Track');
    }
}
