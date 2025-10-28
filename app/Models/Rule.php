<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rule extends Model
{
    use HasFactory;

    protected $table = 'rules'; // Nama tabel
    protected $primaryKey = 'Id_Rule'; // Nama primary key

    public $timestamps = false; // Jika tabel tidak memiliki created_at dan updated_at

    protected $fillable = [
        'Type_Rule',
        'Rule_Rule'
    ];

    // ✅ Casting kolom Rule_Rule sebagai array
    protected $casts = [
        'Rule_Rule' => 'array', // Laravel otomatis encode/decode JSON
    ];

    // public function user()
    // {
    //     return $this->belongsTo(User::class, 'Id_User', 'Id_User');
    // }
}
