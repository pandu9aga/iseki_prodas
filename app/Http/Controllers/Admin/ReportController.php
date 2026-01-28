<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Plan;
use App\Models\Rule;
use App\Models\Efficiency_Area;
use App\Models\Efficiency_Scan;
use Yajra\DataTables\Facades\DataTables;

class ReportController extends Controller
{
    public function lineoff(){
        $page = "report";
        $sub = "lineoff";

        $Id_User = session('Id_User');
        $user = User::find($Id_User);

        return view('admins.reports.lineoff', compact('page', 'sub', 'user'));
    }

    public function getLineoffs()
    {
        $query = Plan::select([
            'Id_Plan',
            'Type_Plan',
            'Sequence_No_Plan',
            'Production_Date_Plan',
            'Model_Name_Plan',
            'Production_No_Plan',
            'Chasis_No_Plan',
            'Model_Label_Plan',
            'Safety_Frame_Label_Plan',
            'Model_Mower_Plan',
            'Mower_No_Plan',
            'Model_Collector_Plan',
            'Collector_No_Plan',
            'Lineoff_Plan',
            'Record_Plan'
        ])
        ->whereNotNull('Lineoff_Plan')
        ->orderBy('Lineoff_Plan', 'desc');

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('Process', function ($row) {
                // Ambil rule langsung di sini (tidak dari addColumn lain)
                $ruleModel = Rule::where('Type_Rule', $row->Model_Name_Plan)->first();

                $rules = [];
                if ($ruleModel && !empty($ruleModel->Rule_Rule)) {
                    $rules = is_array($ruleModel->Rule_Rule)
                        ? $ruleModel->Rule_Rule
                        : json_decode($ruleModel->Rule_Rule, true);
                }

                // Decode Record_Plan (HTML entities → JSON)
                $record = [];
                if (!empty($row->Record_Plan)) {
                    $decoded = json_decode(htmlspecialchars_decode($row->Record_Plan), true);
                    $record = is_array($decoded) ? $decoded : [];
                }

                // Urutkan rules numerik
                ksort($rules, SORT_NUMERIC);

                // Bangun HTML list
                $processHtml = '<ul class="list-unstyled mb-0">';
                if (empty($rules)) {
                    $processHtml .= '<li class="text-muted">Tidak ada rule</li>';
                } else {
                    foreach ($rules as $processName) {
                        $timestamp = $record[$processName] ?? 'belum';
                        $statusClass = $timestamp === 'belum' ? 'text-danger' : 'text-success';
                        $processHtml .= "<li class=\"{$statusClass}\">{$processName}: {$timestamp}</li>";
                    }
                }
                $processHtml .= '</ul>';

                return $processHtml;
            })
            ->rawColumns(['Process'])
            ->make(true);
    }

    public function filter(){
        $page = "report";
        $sub = "filter";

        $Id_User = session('Id_User');
        $user = User::find($Id_User);

        return view('admins.reports.filter', compact('page', 'sub', 'user'));
    }

    public function getFilters(Request $request)
    {
        $type = $request->input('type', 'unit'); // default: unit
        $min = $request->input('min');
        $max = $request->input('max');
        // --- TAMBAHAN: Ambil parameter tahun ---
        $tahun = $request->input('tahun'); // Ambil dari request DataTables

        $query = Plan::select([
                'Id_Plan',
                'Type_Plan',
                'Sequence_No_Plan',
                'Production_Date_Plan',
                'Model_Name_Plan',
                'Production_No_Plan',
                'Chasis_No_Plan',
                'Model_Label_Plan',
                'Safety_Frame_Label_Plan',
                'Model_Mower_Plan',
                'Mower_No_Plan',
                'Model_Collector_Plan',
                'Collector_No_Plan',
                'Lineoff_Plan',
                'Record_Plan'
            ])
            ->when($type === 'unit', function ($q) {
                // Hanya data tanpa huruf T/t
                $q->where('Sequence_No_Plan', 'NOT REGEXP', '^[Tt]');
            })
            ->when($type === 'nonunit', function ($q) {
                // Hanya data yang diawali huruf T/t
                $q->where('Sequence_No_Plan', 'REGEXP', '^[Tt]');
            });

        // --- TAMBAHAN: Filter berdasarkan tahun ---
        if ($tahun) {
            // Konversi tahun ke format yang sesuai untuk pencocokan di database
            $startOfYear = (int)($tahun . '0101'); // 20250101
            $endOfYear = (int)($tahun . '1231');   // 20251231
            $query->whereBetween('Production_Date_Plan', [$startOfYear, $endOfYear]);
        }
        // --- AKHIR TAMBAHAN ---

        // ✅ Filter berdasarkan range
        if ($min && $max) {
            if ($type === 'unit') {
                $query->whereBetween(DB::raw('CAST(Sequence_No_Plan AS UNSIGNED)'), [$min, $max]);
            } else {
                $query->whereRaw("CAST(SUBSTRING(Sequence_No_Plan, 2) AS UNSIGNED) BETWEEN ? AND ?", [$min, $max]);
            }
        }

        // ✅ Urutkan descending berdasarkan angka numerik di Sequence_No_Plan
        if ($type === 'unit') {
            $query->orderByRaw("CAST(Sequence_No_Plan AS UNSIGNED) DESC");
        } else {
            $query->orderByRaw("CAST(SUBSTRING(Sequence_No_Plan, 2) AS UNSIGNED) DESC");
        }

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('Process', function ($row) {
                $ruleModel = Rule::where('Type_Rule', $row->Model_Name_Plan)->first();

                $rules = [];
                if ($ruleModel && !empty($ruleModel->Rule_Rule)) {
                    $rules = is_array($ruleModel->Rule_Rule)
                        ? $ruleModel->Rule_Rule
                        : json_decode($ruleModel->Rule_Rule, true);
                }

                $record = [];
                if (!empty($row->Record_Plan)) {
                    $decoded = json_decode(htmlspecialchars_decode($row->Record_Plan), true);
                    $record = is_array($decoded) ? $decoded : [];
                }

                ksort($rules, SORT_NUMERIC);
                $processHtml = '<ul class="list-unstyled mb-0">';
                if (empty($rules)) {
                    $processHtml .= '<li class="text-muted">Tidak ada rule</li>';
                } else {
                    foreach ($rules as $processName) {
                        $timestamp = $record[$processName] ?? 'belum';
                        $statusClass = $timestamp === 'belum' ? 'text-danger' : 'text-success';
                        $processHtml .= "<li class=\"{$statusClass}\">{$processName}: {$timestamp}</li>";
                    }
                }
                $processHtml .= '</ul>';
                return $processHtml;
            })
            ->rawColumns(['Process'])
            ->make(true);
    }

    public function missing(){
        $page = "report";
        $sub = "missing";

        $Id_User = session('Id_User');
        $user = User::find($Id_User);

        return view('admins.reports.missing', compact('page', 'sub', 'user'));
    }

    public function getMissings()
    {
        $now = Carbon::now();

        $query = Plan::select([
                'Id_Plan',
                'Type_Plan',
                'Sequence_No_Plan',
                'Production_Date_Plan',
                'Model_Name_Plan',
                'Production_No_Plan',
                'Chasis_No_Plan',
                'Model_Label_Plan',
                'Safety_Frame_Label_Plan',
                'Model_Mower_Plan',
                'Mower_No_Plan',
                'Model_Collector_Plan',
                'Collector_No_Plan',
                'Lineoff_Plan',
                'Record_Plan',
                'Status_Plan' // Tambahkan kolom Status_Plan ke SELECT agar bisa di-filter
            ])
            ->whereNotNull('Lineoff_Plan') // Lineoff_Plan != NULL
            ->where('Status_Plan', '!=', 'done') // Status_Plan != 'done'
            ->where(function ($q) use ($now) {
                // Hitung hanya hari kerja (Senin-Jumat) antara Lineoff_Plan dan sekarang
                $q->whereRaw("
                    (
                        -- Hitung total hari antara Lineoff_Plan dan sekarang
                        DATEDIFF(?, Lineoff_Plan)
                        -- Kurangi jumlah hari Sabtu dan Minggu di antara tanggal tersebut
                        - (
                            WEEKDAY(Lineoff_Plan) + 1 + (DATEDIFF(?, Lineoff_Plan) + 1) DIV 7 * 2
                            + CASE WHEN WEEKDAY(Lineoff_Plan) = 6 THEN 1 ELSE 0 END
                            + CASE WHEN WEEKDAY(?) = 5 THEN 1 ELSE 0 END
                            - 2
                        )
                    ) > 2
                ", [
                    $now, $now, $now // Kirim $now tiga kali karena digunakan tiga kali di query raw
                ]);
            })
            ->orderBy('Lineoff_Plan', 'desc');

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('Process', function ($row) {
                // Ambil rule langsung di sini (tidak dari addColumn lain)
                $ruleModel = Rule::where('Type_Rule', $row->Model_Name_Plan)->first();

                $rules = [];
                if ($ruleModel && !empty($ruleModel->Rule_Rule)) {
                    $rules = is_array($ruleModel->Rule_Rule)
                        ? $ruleModel->Rule_Rule
                        : json_decode($ruleModel->Rule_Rule, true);
                }

                // Decode Record_Plan (HTML entities -> JSON)
                $record = [];
                if (!empty($row->Record_Plan)) {
                    $decoded = json_decode(htmlspecialchars_decode($row->Record_Plan), true);
                    $record = is_array($decoded) ? $decoded : [];
                }

                // Urutkan rules numerik
                ksort($rules, SORT_NUMERIC);

                // Bangun HTML list
                $processHtml = '<ul class="list-unstyled mb-0">';
                if (empty($rules)) {
                    $processHtml .= '<li class="text-muted">Tidak ada rule</li>';
                } else {
                    foreach ($rules as $processName) {
                        $timestamp = $record[$processName] ?? 'belum';
                        $statusClass = $timestamp === 'belum' ? 'text-danger' : 'text-success';
                        $processHtml .= "<li class=\"{$statusClass}\">{$processName}: {$timestamp}</li>";
                    }
                }
                $processHtml .= '</ul>';

                return $processHtml;
            })
            ->rawColumns(['Process'])
            ->make(true);
    }

    public function missingExport()
    {
        $now = Carbon::now();

        $plans = Plan::select([
            'Id_Plan',
            'Type_Plan',
            'Sequence_No_Plan',
            'Production_Date_Plan',
            'Model_Name_Plan',
            'Lineoff_Plan',
            'Record_Plan',
            'Status_Plan'
        ])
        ->whereNotNull('Lineoff_Plan')
        ->where('Status_Plan', '!=', 'done')
        ->where(function ($q) use ($now) {
            $q->whereRaw("
                (
                    DATEDIFF(?, Lineoff_Plan)
                    - (
                        WEEKDAY(Lineoff_Plan) + 1 + (DATEDIFF(?, Lineoff_Plan) + 1) DIV 7 * 2
                        + CASE WHEN WEEKDAY(Lineoff_Plan) = 6 THEN 1 ELSE 0 END
                        + CASE WHEN WEEKDAY(?) = 5 THEN 1 ELSE 0 END
                        - 2
                    )
                ) > 2
            ", [$now, $now, $now]);
        })
        ->orderBy('Lineoff_Plan', 'desc')
        ->get();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Header utama
        $headers = [
            'A1' => 'No',
            'B1' => 'Sequence No',
            'C1' => 'Lineoff Date',
            'D1' => 'Type',
            'E1' => 'Model'
        ];

        // Tulis header awal
        foreach ($headers as $cell => $value) {
            $sheet->setCellValue($cell, $value);
        }

        $rowNumber = 2;
        $maxMissingCols = 0; // untuk mengetahui kolom max yang dibutuhkan

        foreach ($plans as $index => $plan) {
            // Ambil rule
            $ruleModel = Rule::where('Type_Rule', $plan->Model_Name_Plan)->first();
            $rules = [];
            if ($ruleModel && !empty($ruleModel->Rule_Rule)) {
                $rules = is_array($ruleModel->Rule_Rule)
                    ? $ruleModel->Rule_Rule
                    : json_decode($ruleModel->Rule_Rule, true);
            }
            ksort($rules, SORT_NUMERIC);

            // Decode record plan
            $record = [];
            if (!empty($plan->Record_Plan)) {
                $decoded = json_decode(htmlspecialchars_decode($plan->Record_Plan), true);
                $record = is_array($decoded) ? $decoded : [];
            }

            // Cari missing process
            $missing = [];
            foreach ($rules as $processName) {
                if (!isset($record[$processName])) {
                    $missing[] = $processName;
                }
            }

            // Update max kolom yang diperlukan
            if (count($missing) > $maxMissingCols) {
                $maxMissingCols = count($missing);
            }

            // Isi data umum
            $sheet->setCellValue("A{$rowNumber}", $index + 1);
            $sheet->setCellValue("B{$rowNumber}", $plan->Sequence_No_Plan);
            $sheet->setCellValue("C{$rowNumber}", $plan->Lineoff_Plan);
            $sheet->setCellValue("D{$rowNumber}", $plan->Type_Plan);
            $sheet->setCellValue("E{$rowNumber}", $plan->Model_Name_Plan);

            // Isi kolom missing (mulai dari F)
            $col = 'F';
            foreach ($missing as $miss) {
                $sheet->setCellValue("{$col}{$rowNumber}", $miss);
                $col++;
            }

            $rowNumber++;
        }

        // Tambahkan header untuk Missing Process dinamis
        $col = 'F';
        for ($i = 1; $i <= $maxMissingCols; $i++) {
            $sheet->setCellValue("{$col}1", "Missing {$i}");
            $col++;
        }

        // Tentukan range header (misal A1 sampai kolom terakhir)
        $lastCol = chr(ord('E') + $maxMissingCols);
        $headerRange = "A1:{$lastCol}1";

        // Styling header: bold, center, background color, border, filter
        $headerStyle = [
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF']
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                'vertical'   => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'color' => ['rgb' => '4F81BD'] // biru lembut
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['rgb' => '000000']
                ]
            ]
        ];

        $sheet->getStyle($headerRange)->applyFromArray($headerStyle);

        // Aktifkan AutoFilter di baris header
        $sheet->setAutoFilter($headerRange);

        // Auto width untuk semua kolom
        foreach (range('A', $lastCol) as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Freeze header baris pertama
        $sheet->freezePane('A2');

        // Buat file untuk diunduh
        $fileName = 'missing_processes_' . $now->format('Ymd_His') . '.xlsx';
        $writer = new Xlsx($spreadsheet);
        $filePath = storage_path("app/public/{$fileName}");
        $writer->save($filePath);

        return response()->download($filePath)->deleteFileAfterSend(true);
    }

    public function areaReport()
    {
        $page = "report";
        $sub = "area";

        $Id_User = session('Id_User');
        $user = User::find($Id_User);

        // Ambil semua area dari Efficiency_Area
        $areas = Efficiency_Area::select('Id_Area', 'Name_Area')
            ->orderBy('Name_Area')
            ->get();

        return view('admins.reports.area', compact('page', 'sub', 'user', 'areas'));
    }

    public function getAreaReports(Request $request)
    {
        $areaId = $request->input('area_id');
        
        // Cek apakah area yang dipilih adalah DAIICHI (hardcoded ID 999)
        if ($areaId == 999) {
            // Gunakan logika DAIICHI - ambil dari tabel Plan berdasarkan Daiichi_Record
            $query = Plan::select([
                'Id_Plan',
                'Type_Plan',
                'Sequence_No_Plan',
                'Production_Date_Plan',
                'Model_Name_Plan',
                'Production_No_Plan',
                'Chasis_No_Plan',
                'Model_Label_Plan',
                'Safety_Frame_Label_Plan',
                'Model_Mower_Plan',
                'Mower_No_Plan',
                'Model_Collector_Plan',
                'Collector_No_Plan',
                'Daiichi_Record'
            ])
            ->whereNotNull('Daiichi_Record');

            // Filter berdasarkan tanggal Daiichi_Record
            if ($request->filled('scan_date')) {
                $query->whereDate('Daiichi_Record', $request->scan_date);
            } else {
                $query->whereDate('Daiichi_Record', Carbon::today()->toDateString());
            }

            $query->orderBy('Daiichi_Record', 'desc');

            $results = $query->get();

            // Tambahkan field untuk konsistensi dengan area lain
            $results = $results->map(function($plan) {
                $plan->Assigned_Hour_Scan = '-'; // DAIICHI tidak memiliki hour
                $plan->Time_Scan = $plan->Daiichi_Record; // Gunakan Daiichi_Record sebagai Time_Scan
                return $plan;
            });

            return DataTables::of($results)
                ->addIndexColumn()
                ->make(true);
        }
        
        // Cek apakah area yang dipilih adalah MAIN LINE
        $area = Efficiency_Area::find($areaId);
        
        if ($area && $area->Name_Area === 'MAIN LINE') {
            // Gunakan logika MAIN LINE dari AreaController
            $query = Plan::select([
                'Id_Plan',
                'Type_Plan',
                'Sequence_No_Plan',
                'Production_Date_Plan',
                'Model_Name_Plan',
                'Production_No_Plan',
                'Chasis_No_Plan',
                'Model_Label_Plan',
                'Safety_Frame_Label_Plan',
                'Model_Mower_Plan',
                'Mower_No_Plan',
                'Model_Collector_Plan',
                'Collector_No_Plan',
                'Lineoff_Plan'
            ])
            ->whereNotNull('Lineoff_Plan');

            // Filter berdasarkan tanggal lineoff
            if ($request->filled('scan_date')) {
                $query->whereDate('Lineoff_Plan', $request->scan_date);
            } else {
                $query->whereDate('Lineoff_Plan', Carbon::today()->toDateString());
            }

            $query->orderBy('Lineoff_Plan', 'desc');

            $results = $query->get();

            // Tambahkan Assigned_Hour_Scan dari Efficiency_Scan
            $results = $results->map(function($plan) use ($areaId) {
                $scan = Efficiency_Scan::where(
                    DB::connection('efficiency')->raw('LPAD(Sequence_No_Plan, 5, "0")'),
                    '=',
                    str_pad($plan->Sequence_No_Plan, 5, '0', STR_PAD_LEFT)
                )
                ->where('Production_Date_Plan', $plan->Production_Date_Plan)
                ->where('Id_Area', $areaId)
                ->orderBy('Time_Scan', 'desc')
                ->first();

                $plan->Assigned_Hour_Scan = $scan ? $scan->Assigned_Hour_Scan : '-';
                $plan->Time_Scan = $plan->Lineoff_Plan; // Untuk konsistensi dengan area lain
                return $plan;
            });

            return DataTables::of($results)
                ->addIndexColumn()
                ->make(true);
        }

        // Logika area biasa (selain MAIN LINE dan DAIICHI)
        $plansSubquery = Plan::select(
            'Sequence_No_Plan',
            'Production_Date_Plan',
            'Id_Plan',
            'Type_Plan',
            'Model_Name_Plan',
            'Production_No_Plan',
            'Chasis_No_Plan',
            'Model_Label_Plan',
            'Safety_Frame_Label_Plan',
            'Model_Mower_Plan',
            'Mower_No_Plan',
            'Model_Collector_Plan',
            'Collector_No_Plan',
            'Lineoff_Plan'
        );

        $query = Efficiency_Scan::select(
            'scans.Sequence_No_Plan',
            'scans.Production_Date_Plan',
            DB::raw('MAX(scans.Time_Scan) as Time_Scan'),
            DB::raw('SUM(scans.Assigned_Hour_Scan) as Assigned_Hour_Scan'),
            'plans.Id_Plan',
            'plans.Type_Plan',
            'plans.Model_Name_Plan',
            'plans.Production_No_Plan',
            'plans.Chasis_No_Plan',
            'plans.Model_Label_Plan',
            'plans.Safety_Frame_Label_Plan',
            'plans.Model_Mower_Plan',
            'plans.Mower_No_Plan',
            'plans.Model_Collector_Plan',
            'plans.Collector_No_Plan',
            'plans.Lineoff_Plan'
        )
        ->leftJoinSub(
            $plansSubquery,
            'plans',
            function($join) {
                $join->on('scans.Sequence_No_Plan', '=', 'plans.Sequence_No_Plan')
                    ->on('scans.Production_Date_Plan', '=', 'plans.Production_Date_Plan');
            }
        )
        ->where('scans.Id_Area', $areaId)
        ->groupBy(
            'scans.Sequence_No_Plan',
            'scans.Production_Date_Plan',
            'plans.Id_Plan',
            'plans.Type_Plan',
            'plans.Model_Name_Plan',
            'plans.Production_No_Plan',
            'plans.Chasis_No_Plan',
            'plans.Model_Label_Plan',
            'plans.Safety_Frame_Label_Plan',
            'plans.Model_Mower_Plan',
            'plans.Mower_No_Plan',
            'plans.Model_Collector_Plan',
            'plans.Collector_No_Plan',
            'plans.Lineoff_Plan'
        );

        if ($request->filled('scan_date')) {
            $query->whereDate('scans.Time_Scan', $request->scan_date);
        } else {
            $query->whereDate('scans.Time_Scan', Carbon::today()->toDateString());
        }

        $query->orderBy('Time_Scan', 'desc');

        return DataTables::of($query)
            ->addIndexColumn()
            ->make(true);
    }

    public function exportAreaReport(Request $request)
    {
        $areaId = $request->query('area_id');
        $selectedDate = Carbon::parse($request->query('scan_date'))->startOfDay();

        // Cek apakah area adalah DAIICHI
        if ($areaId == 999) {
            $endDate = $selectedDate->copy()->endOfDay();

            $plans = Plan::select([
                'Type_Plan',
                'Sequence_No_Plan',
                'Production_Date_Plan',
                'Model_Name_Plan',
                'Production_No_Plan',
                'Chasis_No_Plan',
                'Model_Label_Plan',
                'Safety_Frame_Label_Plan',
                'Model_Mower_Plan',
                'Mower_No_Plan',
                'Model_Collector_Plan',
                'Collector_No_Plan',
                'Daiichi_Record'
            ])
            ->whereNotNull('Daiichi_Record')
            ->whereBetween('Daiichi_Record', [$selectedDate, $endDate])
            ->orderBy('Daiichi_Record', 'asc')
            ->get();

            $areaName = 'DAIICHI';
        } else {
            // Ambil nama area
            $area = Efficiency_Area::find($areaId);
            if (!$area) {
                return redirect()->back()->with('error', 'Area tidak ditemukan');
            }
            $areaName = $area->Name_Area;

            // Cek apakah area adalah MAIN LINE
            if ($area->Name_Area === 'MAIN LINE') {
                // Gunakan logika export MAIN LINE
                $endDate = $selectedDate->copy()->endOfDay();

                $plans = Plan::select([
                    'Type_Plan',
                    'Sequence_No_Plan',
                    'Production_Date_Plan',
                    'Model_Name_Plan',
                    'Production_No_Plan',
                    'Chasis_No_Plan',
                    'Model_Label_Plan',
                    'Safety_Frame_Label_Plan',
                    'Model_Mower_Plan',
                    'Mower_No_Plan',
                    'Model_Collector_Plan',
                    'Collector_No_Plan',
                    'Lineoff_Plan'
                ])
                ->whereNotNull('Lineoff_Plan')
                ->whereBetween('Lineoff_Plan', [$selectedDate, $endDate])
                ->orderBy('Lineoff_Plan', 'asc')
                ->get();
            } else {
                // Logika export area biasa
                $plansSubquery = Plan::select(
                    'Sequence_No_Plan',
                    'Production_Date_Plan',
                    'Id_Plan',
                    'Type_Plan',
                    'Model_Name_Plan',
                    'Production_No_Plan',
                    'Chasis_No_Plan',
                    'Model_Label_Plan',
                    'Safety_Frame_Label_Plan',
                    'Model_Mower_Plan',
                    'Mower_No_Plan',
                    'Model_Collector_Plan',
                    'Collector_No_Plan',
                    'Lineoff_Plan'
                );

                $query = Efficiency_Scan::select(
                    'scans.Sequence_No_Plan',
                    'scans.Production_Date_Plan',
                    DB::raw('MAX(scans.Time_Scan) as Time_Scan'),
                    DB::raw('SUM(scans.Assigned_Hour_Scan) as Assigned_Hour_Scan'),
                    'plans.Id_Plan',
                    'plans.Type_Plan',
                    'plans.Model_Name_Plan',
                    'plans.Production_No_Plan',
                    'plans.Chasis_No_Plan',
                    'plans.Model_Label_Plan',
                    'plans.Safety_Frame_Label_Plan',
                    'plans.Model_Mower_Plan',
                    'plans.Mower_No_Plan',
                    'plans.Model_Collector_Plan',
                    'plans.Collector_No_Plan',
                    'plans.Lineoff_Plan'
                )
                ->leftJoinSub(
                    $plansSubquery,
                    'plans',
                    function($join) {
                        $join->on('scans.Sequence_No_Plan', '=', 'plans.Sequence_No_Plan')
                            ->on('scans.Production_Date_Plan', '=', 'plans.Production_Date_Plan');
                    }
                )
                ->where('scans.Id_Area', $areaId)
                ->groupBy(
                    'scans.Sequence_No_Plan',
                    'scans.Production_Date_Plan',
                    'plans.Id_Plan',
                    'plans.Type_Plan',
                    'plans.Model_Name_Plan',
                    'plans.Production_No_Plan',
                    'plans.Chasis_No_Plan',
                    'plans.Model_Label_Plan',
                    'plans.Safety_Frame_Label_Plan',
                    'plans.Model_Mower_Plan',
                    'plans.Mower_No_Plan',
                    'plans.Model_Collector_Plan',
                    'plans.Collector_No_Plan',
                    'plans.Lineoff_Plan'
                );

                if ($request->filled('scan_date')) {
                    $query->whereDate('scans.Time_Scan', $request->scan_date);
                } else {
                    $query->whereDate('scans.Time_Scan', Carbon::today()->toDateString());
                }

                $query->orderBy('Time_Scan', 'desc');

                $plans = $query->get();
            }
        }

        $totalPlans = $plans->count();

        $typeCounts = $plans->groupBy('Type_Plan')->map(function ($group) {
            return $group->count();
        });
        $sortedTypeCounts = $typeCounts->toArray();
        ksort($sortedTypeCounts);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // --- ISI DATA KE SPREADSHEET ---
        $currentRow = 2;

        // 0. Judul Area (Pink Cell)
        $sheet->setCellValue('B' . $currentRow, 'Area Scan:');
        $sheet->setCellValue('C' . $currentRow, $areaName);
        $this->applyPinkCellStyle($sheet, 'B' . $currentRow . ':C' . $currentRow);
        $this->applyTableBorder($sheet, 'B' . $currentRow . ':C' . $currentRow);
        $currentRow++;

        // 1. Judul Tanggal (Pink Cell)
        $sheet->setCellValue('B' . $currentRow, 'Tanggal Scan:');
        $sheet->setCellValue('C' . $currentRow, $selectedDate->format('d F Y'));
        $this->applyPinkCellStyle($sheet, 'B' . $currentRow . ':C' . $currentRow);
        $this->applyTableBorder($sheet, 'B' . $currentRow . ':C' . $currentRow);
        $currentRow++;

        // 2. Update Data Per (Pink Cell)
        $sheet->setCellValue('B' . $currentRow, 'Update Data Per:');
        $sheet->setCellValue('C' . $currentRow, Carbon::now()->format('d F Y H:i:s'));
        $this->applyTableBorder($sheet, 'B' . $currentRow . ':C' . $currentRow);
        $currentRow++;

        // 3. Total Keseluruhan Data (Baris ke-3, Pink Cell)
        $sheet->setCellValue('B' . $currentRow, 'Total Keseluruhan Data:');
        $sheet->setCellValue('C' . $currentRow, $totalPlans);
        $style = $sheet->getStyle('C' . $currentRow);
        $style->getFont()->setSize(14)->setBold(true);
        $this->applyTableBorder($sheet, 'B' . $currentRow . ':C' . $currentRow);
        $currentRow += 2;

        // 4. Header Tabel Data - Baris ke-5 (Pink Header)
        // Untuk MAIN LINE dan DAIICHI, tidak ada kolom Hour
        if ($areaId == 999 || (isset($area) && $area->Name_Area === 'MAIN LINE')) {
            $headers = [
                'No', 'Sequence No', 'Model Name', 'Type', 'Production No', 'Production Date', 'Scan', 'Chasis No',
                'Model Label', 'Safety Frame Label', 'Model Mower', 'Mower No', 'Model Collector', 'Collector No'
            ];
        } else {
            $headers = [
                'No', 'Sequence No', 'Model Name', 'Type', 'Hour', 'Production No', 'Production Date', 'Scan', 'Chasis No',
                'Model Label', 'Safety Frame Label', 'Model Mower', 'Mower No', 'Model Collector', 'Collector No'
            ];
        }
        
        $colIndex = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($colIndex . $currentRow, $header);
            $colIndex++;
        }
        $tableHeaderRow = $currentRow;
        $lastHeaderCol = chr(ord('A') + count($headers) - 1);
        $this->applyPinkHeaderStyle($sheet, 'A' . $tableHeaderRow . ':' . $lastHeaderCol . $tableHeaderRow);
        $currentRow++;

        // 5. Isi Data Tabel
        $no = 1;
        foreach ($plans as $plan) {
            $colIndex = 'A';
            $sheet->setCellValue($colIndex . $currentRow, $no); 
            $sheet->getStyle($colIndex . $currentRow)->getAlignment()->setHorizontal('center'); 
            $colIndex++;
            
            $sheet->setCellValue($colIndex . $currentRow, $plan->Sequence_No_Plan); 
            $sheet->getStyle($colIndex . $currentRow)->getAlignment()->setHorizontal('left'); 
            $colIndex++;
            
            $sheet->setCellValue($colIndex . $currentRow, $plan->Model_Name_Plan); 
            $sheet->getStyle($colIndex . $currentRow)->getAlignment()->setHorizontal('left'); 
            $colIndex++;
            
            $sheet->setCellValue($colIndex . $currentRow, $plan->Type_Plan); 
            $sheet->getStyle($colIndex . $currentRow)->getAlignment()->setHorizontal('left'); 
            $colIndex++;
            
            // Hanya tampilkan Hour jika bukan MAIN LINE dan bukan DAIICHI
            if ($areaId != 999 && (!isset($area) || $area->Name_Area !== 'MAIN LINE')) {
                $sheet->setCellValue($colIndex . $currentRow, $plan->Assigned_Hour_Scan ?? '-'); 
                $sheet->getStyle($colIndex . $currentRow)->getAlignment()->setHorizontal('left'); 
                $colIndex++;
            }
            
            $sheet->setCellValue($colIndex . $currentRow, $plan->Production_No_Plan); 
            $sheet->getStyle($colIndex . $currentRow)->getAlignment()->setHorizontal('left'); 
            $colIndex++;
            
            $sheet->setCellValue($colIndex . $currentRow, $plan->Production_Date_Plan); 
            $sheet->getStyle($colIndex . $currentRow)->getAlignment()->setHorizontal('left'); 
            $colIndex++;
            
            // Tentukan kolom scan berdasarkan area
            if ($areaId == 999) {
                $scanTime = $plan->Daiichi_Record ?? '-';
            } elseif (isset($area) && $area->Name_Area === 'MAIN LINE') {
                $scanTime = $plan->Lineoff_Plan ?? '-';
            } else {
                $scanTime = $plan->Time_Scan ?? '-';
            }
            $sheet->setCellValue($colIndex . $currentRow, $scanTime); 
            $sheet->getStyle($colIndex . $currentRow)->getAlignment()->setHorizontal('left'); 
            $colIndex++;
            
            $sheet->setCellValue($colIndex . $currentRow, $plan->Chasis_No_Plan); 
            $sheet->getStyle($colIndex . $currentRow)->getAlignment()->setHorizontal('left'); 
            $colIndex++;
            
            $sheet->setCellValue($colIndex . $currentRow, $plan->Model_Label_Plan); 
            $sheet->getStyle($colIndex . $currentRow)->getAlignment()->setHorizontal('left'); 
            $colIndex++;
            
            $sheet->setCellValue($colIndex . $currentRow, $plan->Safety_Frame_Label_Plan); 
            $sheet->getStyle($colIndex . $currentRow)->getAlignment()->setHorizontal('left'); 
            $colIndex++;
            
            $sheet->setCellValue($colIndex . $currentRow, $plan->Model_Mower_Plan); 
            $sheet->getStyle($colIndex . $currentRow)->getAlignment()->setHorizontal('left'); 
            $colIndex++;
            
            $sheet->setCellValue($colIndex . $currentRow, $plan->Mower_No_Plan); 
            $sheet->getStyle($colIndex . $currentRow)->getAlignment()->setHorizontal('left'); 
            $colIndex++;
            
            $sheet->setCellValue($colIndex . $currentRow, $plan->Model_Collector_Plan); 
            $sheet->getStyle($colIndex . $currentRow)->getAlignment()->setHorizontal('left'); 
            $colIndex++;
            
            $sheet->setCellValue($colIndex . $currentRow, $plan->Collector_No_Plan); 
            $sheet->getStyle($colIndex . $currentRow)->getAlignment()->setHorizontal('left'); 
            $colIndex++;
            
            $no++;
            $currentRow++;
        }
        $lastDataRow = $currentRow - 1;

        // --- STYLING TABEL DATA ---
        $this->applyTableBorder($sheet, 'A' . $tableHeaderRow . ':' . $lastHeaderCol . $lastDataRow);
        $sheet->setAutoFilter('A' . $tableHeaderRow . ':' . $lastHeaderCol . $lastDataRow);

        foreach (range('A', $lastHeaderCol) as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
        // --- AKHIR STYLING TABEL DATA ---

        // 6. Kolom Rekap: Header Rekap Tipe
        $rekapHeaderRow = 5;
        $rekapStartCol = chr(ord($lastHeaderCol) + 2); // 2 kolom setelah tabel data
        $rekapEndCol = chr(ord($rekapStartCol) + 1);
        
        $sheet->setCellValue($rekapStartCol . $rekapHeaderRow, 'Type:');
        $this->applyPinkHeaderStyle($sheet, $rekapStartCol . $rekapHeaderRow . ':' . $rekapEndCol . $rekapHeaderRow);

        // 7. Isi Rekap Tipe & Jumlah
        $currentRekapRow = $rekapHeaderRow + 1;
        foreach ($sortedTypeCounts as $type => $count) {
            $sheet->setCellValue($rekapStartCol . $currentRekapRow, $type);
            $sheet->setCellValue($rekapEndCol . $currentRekapRow, $count);
            $currentRekapRow++;
        }

        // --- STYLING REKAP TIPE ---
        $this->applyTableBorder($sheet, $rekapStartCol . ($rekapHeaderRow + 1) . ':' . $rekapEndCol . ($currentRekapRow - 1));

        $sheet->setCellValue($rekapStartCol . $currentRekapRow, 'Total Keseluruhan:');
        $sheet->setCellValue($rekapEndCol . $currentRekapRow, $totalPlans);
        $style = $sheet->getStyle($rekapStartCol . $currentRekapRow . ':' . $rekapEndCol . $currentRekapRow);
        $style->getFont()->setBold(true);
        $style->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('FFC0CB');
        $style->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

        foreach ([$rekapStartCol, $rekapEndCol] as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
        // --- AKHIR STYLING REKAP TIPE ---

        // --- OUTPUT KE BROWSER ---
        $fileName = 'Report_' . $areaName . '_' . $selectedDate->format('Y-m-d') . '.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $fileName . '"');
        header('Cache-Control: max-age=0');
        if (ob_get_level()) {
            ob_end_clean();
        }

        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');

        exit();
    }

    // --- FUNGSI BANTU UNTUK STYLING ---
    private function applyPinkHeaderStyle($sheet, $range) {
        $style = $sheet->getStyle($range);
        $style->getFont()->setBold(true);
        $style->getAlignment()->setHorizontal('center');
        $style->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('FFC0CB'); // Pink muda
        $style->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
    }

    private function applyPinkCellStyle($sheet, $range) {
        $style = $sheet->getStyle($range);
        $style->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('FFC0CB'); // Pink muda
        $style->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
    }

    private function applyTableBorder($sheet, $range) {
        $sheet->getStyle($range)->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
    }
    // --- AKHIR FUNGSI BANTU ---

}