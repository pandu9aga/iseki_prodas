<?php

namespace App\Http\Controllers\Area;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Plan;
use Carbon\Carbon;

class AreaController extends Controller
{
    public function index()
    {
        return view('layouts.area');
    }

    public function dashboard()
    {
        $today = Carbon::today();

        $year = $today->format('Y');      // contoh: 2025
        $month = $today->format('Ym');    // contoh: 202509

        // Annual count
        $annualCount = DB::table('plans')
            ->where('Production_Date_Plan', 'like', "%$year%")
            ->count();

        // Monthly count
        $monthlyCount = DB::table('plans')
            ->where('Production_Date_Plan', 'like', "%$month%")
            ->count();

        // Type stats bulan ini
        $typeStatsMonthly = DB::table('plans')
            ->select('Type_Plan', DB::raw('COUNT(*) as total'))
            ->where('Production_Date_Plan', 'like', "%$month%")
            ->groupBy('Type_Plan')
            ->get();

        // warna (biar konsisten)
        $colors = ['primary', 'success', 'info', 'warning', 'danger', 'secondary'];

        // gabungin ke json
        $jsonData = [
            'year' => $year,
            'annualCount' => $annualCount,
            'monthlyCount' => $monthlyCount,
            'types' => $typeStatsMonthly->map(function ($stat, $i) use ($colors) {
                return [
                    'type' => $stat->Type_Plan,
                    'total' => $stat->total,
                    'color' => $colors[$i % count($colors)],
                ];
            }),
        ];

        return response()->json($jsonData);
    }

    public function dashboard2()
    {
        $today = Carbon::today();

        $year = $today->format('Y');

        // Ambil data per bulan (YYYYMM) + Type_Plan
        $plans = DB::table('plans')
            ->select(
                DB::raw("SUBSTRING(Production_Date_Plan, 1, 6) as ym"), // YYYYMM
                'Type_Plan',
                DB::raw('COUNT(*) as total')
            )
            ->where('Production_Date_Plan', 'like', $year . '%')
            ->groupBy('ym', 'Type_Plan')
            ->orderBy('ym')
            ->get();

        // Ambil semua bulan yang muncul
        $months = $plans->pluck('ym')->unique()->values();

        // Ambil semua tipe
        $types = $plans->pluck('Type_Plan')->unique()->values();

        // Format untuk chart (stacked bar butuh data series per type)
        $series = $types->map(function ($type) use ($plans, $months) {
            return [
                'name' => $type,
                'data' => $months->map(function ($month) use ($plans, $type) {
                    $record = $plans->firstWhere(fn($p) => $p->ym == $month && $p->Type_Plan == $type);
                    return $record ? $record->total : 0;
                }),
            ];
        });

        // Konversi YYYYMM ke nama bulan singkat
        $monthLabels = $months->map(function ($ym) {
            $carbon = Carbon::createFromFormat('Ym', $ym);
            return $carbon->format('M'); // Jan, Feb, Mar ...
        });

        return response()->json([
            'months' => $monthLabels,
            'series' => $series,
        ]);
    }

    public function dashboard3()
    {
        $today = Carbon::today(); // Mewakili 00:00:00 pada hari ini
        $todayFormatted = $today->format('Y-m-d'); // Format tanggal: YYYY-MM-DD

        $todayLineoffCount = DB::table('plans')
            ->whereDate('Lineoff_Plan', $todayFormatted) // <-- Perubahan di sini
            ->count();

        $missingUnitCount = DB::table('plans')
            ->whereNotNull('Lineoff_Plan')
            ->where('Status_Plan', '!=', 'done')
            ->count();

        return response()->json([
            'success' => true,
            'data' => [
                'today_lineoff' => $todayLineoffCount,
                'missing_unit' => $missingUnitCount,
            ]
        ]);
    }
}
