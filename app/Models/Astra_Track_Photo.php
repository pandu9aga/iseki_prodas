<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Astra_Track_Photo extends Model
{
    use HasFactory;

    protected $connection = 'astra';
    protected $table = 'track_photos'; // Nama tabel
    protected $primaryKey = 'Id_Track_Photo'; // Nama primary key

    public $timestamps = false; // Jika tabel tidak memiliki created_at dan updated_at

    protected $fillable = [
        'Id_Track',
        'Name_Photo_Angle',
        'Icon_Photo_Angle',
        'Path_Track_Photo'
    ];

    public function track()
    {
        return $this->belongsTo(Astra_Track::class, 'Id_Track', 'Id_Track');
    }
}
