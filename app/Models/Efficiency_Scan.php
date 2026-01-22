<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Efficiency_Scan extends Model
{
    protected $connection = 'efficiency'; // Menentukan koneksi database khusus
    protected $table = 'scans';
    protected $primaryKey = 'Id_Scan';
    public $timestamps = false;

    // Hapus Area_Scan, tambahkan Id_Area dan Id_Daily_Job
    protected $fillable = [
        // 'Area_Scan', // Dihapus
        'Id_Member',
        'Id_Tractor',
        'Time_Scan',
        'Assigned_Hour_Scan',
        'Sequence_No_Plan',
        'Production_Date_Plan',
        'Id_Area',       // Ditambahkan
        'Id_Daily_Job', // Ditambahkan
        'Nik_Replace'
    ];

    public function member()
    {
        return $this->belongsTo(Efficiency_Member::class, 'Id_Member', 'id');
    }

    public function tractor()
    {
        return $this->belongsTo(Efficiency_Tractor::class, 'Id_Tractor', 'Id_Tractor');
    }

    // Relasi ke DailyJob
    // public function dailyJob()
    // {
    //     return $this->belongsTo(DailyJob::class, 'Id_Daily_Job', 'Id_Daily_Job');
    // }

    // Relasi ke Area (bisa dari Id_Area di scan atau dari dailyJob)
    // Jika Id_Area disimpan di scan:
    public function area()
    {
        return $this->belongsTo(Efficiency_Area::class, 'Id_Area', 'Id_Area');
    }

    public function getPlanAttribute()
    {
        $noSeq = $this->Sequence_No_Plan;
        $prodDate = $this->Production_Date_Plan;

        $noSeq5Digit = str_pad($noSeq, 5, '0', STR_PAD_LEFT);

        $plan = Plan::whereRaw('LPAD(?, 5, "0") = Sequence_No_Plan', [$noSeq5Digit])
                    ->where('Production_Date_Plan', $prodDate)
                    ->first();

        return $plan;
    }
}
