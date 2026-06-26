<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Plan;
use App\Models\Efficiency_Scan;
use App\Models\WaRangkumanHistory;
use App\Models\WaRangkumanTarget;
use App\Models\WaRangkumanLog;
use App\Models\WaQueue;

class WaRangkumanController extends Controller
{
    // ══════════════════════════════════════════════
    //  MAIN PAGE
    // ══════════════════════════════════════════════

    public function index()
    {
        $page = 'wa-rangkuman';
        $sub = 'rangkuman';
        $Id_User = session('Id_User');
        $user = User::find($Id_User);
        return view('admins.reports.wa-rangkuman', compact('page', 'sub', 'user'));
    }

    // ══════════════════════════════════════════════
    //  DATA
    // ══════════════════════════════════════════════

    public function getData(Request $request)
    {
        $date = $request->input('date', Carbon::today()->toDateString());
        $carbonDate = Carbon::parse($date);
        $prodDate = $carbonDate->format('Ymd');
        $monthStart = $carbonDate->copy()->startOfMonth();
        $defaultDb = config('database.connections.mysql.database');

        // ── Helper: count distinct scans untuk tanggal tertentu ──
        $scanCount = function ($areaId) use ($carbonDate) {
            return Efficiency_Scan::where('Id_Area', $areaId)
                ->whereDate('Time_Scan', $carbonDate)
                ->distinct('Sequence_No_Plan')
                ->count('Sequence_No_Plan');
        };

        // ── Helper: Cumulative Actual bulanan (dari tgl 1 s/d tgl dipilih) ──
        // Pakai COUNT(DISTINCT DATE, Sequence_No_Plan) biar sequence yg sama di hari
        // berbeda dihitung per hari (sama kaya Σ daily A)
        $monthScanCount = function ($areaId) use ($monthStart, $carbonDate) {
            return Efficiency_Scan::where('Id_Area', $areaId)
                ->whereBetween('Time_Scan', [$monthStart, $carbonDate->copy()->endOfDay()])
                ->selectRaw('COUNT(DISTINCT DATE(Time_Scan), Sequence_No_Plan) as cnt')
                ->value('cnt');
        };

        $monthScanCountWithTractor = function ($areaId, $tractorCondition) use ($monthStart, $carbonDate, $defaultDb) {
            return Efficiency_Scan::from('scans')
                ->join(DB::connection('efficiency')->raw('tractors as t'), 'scans.Id_Tractor', '=', 't.Id_Tractor')
                ->leftJoin(DB::raw("`{$defaultDb}`.`plans` as plans"), function ($join) {
                    $join->on('scans.Sequence_No_Plan', '=', 'plans.Sequence_No_Plan')
                        ->on('scans.Production_Date_Plan', '=', 'plans.Production_Date_Plan');
                })
                ->where('scans.Id_Area', $areaId)
                ->whereBetween('scans.Time_Scan', [$monthStart, $carbonDate->copy()->endOfDay()])
                ->whereRaw($tractorCondition)
                ->selectRaw('COUNT(DISTINCT DATE(scans.Time_Scan), scans.Sequence_No_Plan) as cnt')
                ->value('cnt');
        };

        $monthScanCountWithPlan = function ($areaId, $planCondition) use ($monthStart, $carbonDate, $defaultDb) {
            return Efficiency_Scan::from('scans')
                ->join(DB::connection('efficiency')->raw('tractors as t'), 'scans.Id_Tractor', '=', 't.Id_Tractor')
                ->leftJoin(DB::raw("`{$defaultDb}`.`plans` as plans"), function ($join) {
                    $join->on('scans.Sequence_No_Plan', '=', 'plans.Sequence_No_Plan')
                        ->on('scans.Production_Date_Plan', '=', 'plans.Production_Date_Plan');
                })
                ->where('scans.Id_Area', $areaId)
                ->whereBetween('scans.Time_Scan', [$monthStart, $carbonDate->copy()->endOfDay()])
                ->whereRaw($planCondition)
                ->selectRaw('COUNT(DISTINCT DATE(scans.Time_Scan), scans.Sequence_No_Plan) as cnt')
                ->value('cnt');
        };

        // Lineoff bulanan untuk GT
        $monthLineoffActual = Plan::whereNotNull('Lineoff_Plan')
            ->whereBetween('Lineoff_Plan', [$monthStart, $carbonDate->copy()->endOfDay()])
            ->count();

        $sxg3SfTypes = ["'SXG3'", "'SXG3MW'", "'SXG3日本'", "'SF2'", "'SF2 Trial'", "'SF2CL'", "'SF2CL日本'", "'SF2MW'", "'SF2MW日本'", "'SF2日本'", "'SF5'", "'SF5MW'"];
        $sxg3SfTypesStr = implode(',', $sxg3SfTypes);
        $sxg3SfWhere = "Type_Plan IN ($sxg3SfTypesStr)";
        $notSxg3SfWhere = "Type_Plan NOT IN ($sxg3SfTypesStr)";

        // ── Helper: count distinct scans with LEFT JOIN to plans + tractors ──
        $scanCountWithTractor = function ($areaId, $tractorCondition) use ($carbonDate, $defaultDb) {
            return Efficiency_Scan::from('scans')
                ->join(DB::connection('efficiency')->raw('tractors as t'), 'scans.Id_Tractor', '=', 't.Id_Tractor')
                ->leftJoin(DB::raw("`{$defaultDb}`.`plans` as plans"), function ($join) {
                    $join->on('scans.Sequence_No_Plan', '=', 'plans.Sequence_No_Plan')
                        ->on('scans.Production_Date_Plan', '=', 'plans.Production_Date_Plan');
                })
                ->where('scans.Id_Area', $areaId)
                ->whereDate('scans.Time_Scan', $carbonDate)
                ->whereRaw($tractorCondition)
                ->distinct('scans.Sequence_No_Plan')
                ->count('scans.Sequence_No_Plan');
        };

        // ── Helper: count scans with LEFT JOIN to plans only (for TRANSMISI type filtering) ──
        $scanCountWithPlan = function ($areaId, $planCondition) use ($carbonDate, $defaultDb) {
            return Efficiency_Scan::from('scans')
                ->join(DB::connection('efficiency')->raw('tractors as t'), 'scans.Id_Tractor', '=', 't.Id_Tractor')
                ->leftJoin(DB::raw("`{$defaultDb}`.`plans` as plans"), function ($join) {
                    $join->on('scans.Sequence_No_Plan', '=', 'plans.Sequence_No_Plan')
                        ->on('scans.Production_Date_Plan', '=', 'plans.Production_Date_Plan');
                })
                ->where('scans.Id_Area', $areaId)
                ->whereDate('scans.Time_Scan', $carbonDate)
                ->whereRaw($planCondition)
                ->distinct('scans.Sequence_No_Plan')
                ->count('scans.Sequence_No_Plan');
        };

        // ── Lineoff count ──
        $lineoffActual = Plan::whereNotNull('Lineoff_Plan')
            ->whereDate('Lineoff_Plan', $date)->count();

        $unitSeqWhere = "Sequence_No_Plan NOT REGEXP '[Tt]'";
        $mocolSeqWhere = "Sequence_No_Plan REGEXP '[Tt]'";

        // ── Ambil target dari database untuk tanggal ini (override) ──
        $dbTargets = WaRangkumanTarget::where('Target_Date', $date)
            ->get()
            ->keyBy(fn($t) => $t->Category_Group . '|' . $t->Category_Item);

        $getTarget = function($group, $item) use ($dbTargets) {
            $key = $group . '|' . $item;
            return isset($dbTargets[$key]) ? (int)$dbTargets[$key]->Target : 0;
        };

        // ── Cumulative Target bulanan — GT = CumActual - CumTarget ──
        $monthTargets = WaRangkumanTarget::whereBetween('Target_Date', [$monthStart->toDateString(), $date])
            ->get()
            ->groupBy(fn($t) => $t->Category_Group . '|' . $t->Category_Item)
            ->map(fn($items) => $items->sum('Target'));

        $getMonthTargetSum = function($group, $item) use ($monthTargets) {
            $key = $group . '|' . $item;
            return isset($monthTargets[$key]) ? (int)$monthTargets[$key] : 0;
        };

        // ── Koreksi kumulatif (dari tgl 1 s/d kemarin) ──
        // GT = S(kemarin) + S(sekarang) + Koreksi_total(kemarin)
        $yesterdayStr = $carbonDate->copy()->subDay()->toDateString();
        $monthKoreksi = WaRangkumanHistory::whereBetween('Log_Date', [$monthStart->toDateString(), $yesterdayStr])
            ->whereNotNull('Koreksi')
            ->where('Koreksi', 'REGEXP', '^-?[0-9]+$')
            ->get()
            ->groupBy(fn($h) => $h->Category_Group . '|' . $h->Category_Item)
            ->map(fn($items) => $items->sum(fn($h) => (int)$h->Koreksi));

        $getMonthKoreksiSum = function($group, $item) use ($monthKoreksi) {
            $key = $group . '|' . $item;
            return isset($monthKoreksi[$key]) ? (int)$monthKoreksi[$key] : 0;
        };

        $rows = [
            // ───── TRANSMISI ─────
            [
                'group' => 'TRANSMISI',
                'items' => [
                    $this->makeWaItem('SXG3 & SF', $getTarget('TRANSMISI', 'SXG3 & SF'), $scanCountWithPlan(2, "(plans.Type_Plan IN ($sxg3SfTypesStr))"), $monthScanCountWithPlan(2, "(plans.Type_Plan IN ($sxg3SfTypesStr))") - $getMonthTargetSum('TRANSMISI', 'SXG3 & SF') + $getMonthKoreksiSum('TRANSMISI', 'SXG3 & SF')),
                    $this->makeWaItem('Transmisi', $getTarget('TRANSMISI', 'Transmisi'), $scanCountWithPlan(2, "(plans.Type_Plan IS NULL OR plans.Type_Plan NOT IN ($sxg3SfTypesStr))"), $monthScanCountWithPlan(2, "(plans.Type_Plan IS NULL OR plans.Type_Plan NOT IN ($sxg3SfTypesStr))") - $getMonthTargetSum('TRANSMISI', 'Transmisi') + $getMonthKoreksiSum('TRANSMISI', 'Transmisi')),
                ]
            ],
            // ───── SUB ENGINE ─────
            [
                'group' => 'SUB ENGINE',
                'items' => [
                    $this->makeWaItem('Sub Engine', $getTarget('SUB ENGINE', 'Sub Engine'), $scanCount(6), $monthScanCount(6) - $getMonthTargetSum('SUB ENGINE', 'Sub Engine') + $getMonthKoreksiSum('SUB ENGINE', 'Sub Engine')),
                ]
            ],
            // ───── LINE A ─────
            [
                'group' => 'LINE A',
                'items' => [
                    $this->makeWaItem('Unit', $getTarget('LINE A', 'Unit'), $scanCountWithTractor(3, "t.Name_Tractor = plans.Model_Name_Plan AND (plans.Model_Mower_Plan IS NULL OR t.Name_Tractor != plans.Model_Mower_Plan) AND (plans.Model_Collector_Plan IS NULL OR t.Name_Tractor != plans.Model_Collector_Plan)"), $monthScanCountWithTractor(3, "t.Name_Tractor = plans.Model_Name_Plan AND (plans.Model_Mower_Plan IS NULL OR t.Name_Tractor != plans.Model_Mower_Plan) AND (plans.Model_Collector_Plan IS NULL OR t.Name_Tractor != plans.Model_Collector_Plan)") - $getMonthTargetSum('LINE A', 'Unit') + $getMonthKoreksiSum('LINE A', 'Unit')),
                    $this->makeWaItem('Mocol', $getTarget('LINE A', 'Mocol'), $scanCountWithTractor(3, "(t.Name_Tractor = plans.Model_Mower_Plan OR t.Name_Tractor = plans.Model_Collector_Plan)"), $monthScanCountWithTractor(3, "(t.Name_Tractor = plans.Model_Mower_Plan OR t.Name_Tractor = plans.Model_Collector_Plan)") - $getMonthTargetSum('LINE A', 'Mocol') + $getMonthKoreksiSum('LINE A', 'Mocol')),
                ]
            ],
            // ───── LINE B ─────
            [
                'group' => 'LINE B',
                'items' => [
                    $this->makeWaItem('Line B', $getTarget('LINE B', 'Line B'), $scanCount(4), $monthScanCount(4) - $getMonthTargetSum('LINE B', 'Line B') + $getMonthKoreksiSum('LINE B', 'Line B')),
                ]
            ],
            // ───── SUB ASSY ─────
            [
                'group' => 'SUB ASSY',
                'items' => [
                    $this->makeWaItem('Sub Assy', $getTarget('SUB ASSY', 'Sub Assy'), $scanCount(7), $monthScanCount(7) - $getMonthTargetSum('SUB ASSY', 'Sub Assy') + $getMonthKoreksiSum('SUB ASSY', 'Sub Assy')),
                ]
            ],
            // ───── MAINLINE ─────
            [
                'group' => 'MAIN LINE',
                'items' => [
                    $this->makeWaItem('Mainline', $getTarget('MAIN LINE', 'Mainline'), $lineoffActual, $monthLineoffActual - $getMonthTargetSum('MAIN LINE', 'Mainline') + $getMonthKoreksiSum('MAIN LINE', 'Mainline')),
                ]
            ],
            // ───── INSPEKSI ─────
            [
                'group' => 'INSPEKSI',
                'items' => [
                    $this->makeWaItem('Inspeksi', $getTarget('INSPEKSI', 'Inspeksi'), $scanCount(8), $monthScanCount(8) - $getMonthTargetSum('INSPEKSI', 'Inspeksi') + $getMonthKoreksiSum('INSPEKSI', 'Inspeksi')),
                ]
            ],
            // ───── MOCOL (MOWER) ─────
            [
                'group' => 'MOCOL',
                'items' => [
                    $this->makeWaItem('Unit', $getTarget('MOCOL', 'Unit'), $scanCountWithTractor(1, "scans.Sequence_No_Plan NOT REGEXP '[Tt]'"), $monthScanCountWithTractor(1, "scans.Sequence_No_Plan NOT REGEXP '[Tt]'") - $getMonthTargetSum('MOCOL', 'Unit') + $getMonthKoreksiSum('MOCOL', 'Unit')),
                    $this->makeWaItem('Mower', $getTarget('MOCOL', 'Mower'), $scanCountWithTractor(1, "scans.Sequence_No_Plan REGEXP '[Tt]' AND plans.Model_Name_Plan = plans.Model_Mower_Plan"), $monthScanCountWithTractor(1, "scans.Sequence_No_Plan REGEXP '[Tt]' AND plans.Model_Name_Plan = plans.Model_Mower_Plan") - $getMonthTargetSum('MOCOL', 'Mower') + $getMonthKoreksiSum('MOCOL', 'Mower')),
                    $this->makeWaItem('Collector', $getTarget('MOCOL', 'Collector'), $scanCountWithTractor(1, "scans.Sequence_No_Plan REGEXP '[Tt]' AND plans.Model_Name_Plan = plans.Model_Collector_Plan"), $monthScanCountWithTractor(1, "scans.Sequence_No_Plan REGEXP '[Tt]' AND plans.Model_Name_Plan = plans.Model_Collector_Plan") - $getMonthTargetSum('MOCOL', 'Collector') + $getMonthKoreksiSum('MOCOL', 'Collector')),
                ]
            ],
        ];

        // ── Totals ──
        $totalT = collect($rows)->sum(fn($g) => collect($g['items'])->sum('T'));
        $totalA = collect($rows)->sum(fn($g) => collect($g['items'])->sum('A'));
        $totalS = collect($rows)->sum(fn($g) => collect($g['items'])->sum('S'));
        $totalGT = collect($rows)->sum(fn($g) => collect($g['items'])->sum('GT'));

        return response()->json([
            'rows' => $rows,
            'totals' => ['T' => $totalT, 'A' => $totalA, 'S' => $totalS, 'GT' => $totalGT],
            'date' => $date,
        ]);
    }

    // ── Bantu: buat item WA Rangkuman ──
    private function makeWaItem($label, $T, $A, $GT)
    {
        $T = (int)$T;
        $A = (int)$A;
        $GT = (int)$GT;
        return [
            'label' => $label,
            'T' => $T,
            'A' => $A,
            'S' => $A - $T,
            'GT' => $GT,
            'koreksi' => 0,
            'prevGT' => 0,
        ];
    }

    // ══════════════════════════════════════════════
    //  TARGET MANAGEMENT
    // ══════════════════════════════════════════════

    public function target()
    {
        $page = 'wa-rangkuman';
        $sub = 'target';
        $Id_User = session('Id_User');
        $user = User::find($Id_User);

        $waLogs = WaRangkumanLog::leftJoin('users', 'wa_rangkuman_logs.Created_By', '=', 'users.Id_User')
            ->orderBy('wa_rangkuman_logs.Created_At', 'desc')
            ->limit(20)
            ->get();

        return view('admins.reports.wa-rangkuman-target', compact('page', 'sub', 'user', 'waLogs'));
    }

    public function importTarget(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls|max:5120',
            'month' => 'required|date_format:Y-m',
        ]);

        $month = $request->input('month');
        $file = $request->file('file');
        $spreadsheet = IOFactory::load($file->getPathname());
        $sheet = $spreadsheet->getActiveSheet();
        $data = $sheet->toArray();

        // Cari header
        $headerRow = null;
        $dateCols = [];
        foreach ($data as $ri => $row) {
            $a = trim($row[0] ?? '');
            $b = trim($row[1] ?? '');
            if (($a === 'Category Group' || $a === 'GROUP') && ($b === 'Category Item' || $b === 'ITEM')) {
                $headerRow = $ri;
                for ($c = 2; $c < count($row); $c++) {
                    $val = trim($row[$c] ?? '');
                    if (is_numeric($val) && $val >= 1 && $val <= 31) {
                        $dateCols[$c] = (int)$val;
                    }
                }
                break;
            }
        }

        if ($headerRow === null || empty($dateCols)) {
            return back()->withErrors('Format Excel tidak valid. Header: Category Group | Category Item | 1 | 2 | ... | 31');
        }

        $imported = 0;
        $errors = [];

        for ($ri = $headerRow + 1; $ri < count($data); $ri++) {
            $group = trim($data[$ri][0] ?? '');
            $item = trim($data[$ri][1] ?? '');
            if (empty($group) || empty($item)) continue;

            foreach ($dateCols as $col => $day) {
                $targetVal = trim($data[$ri][$col] ?? '');
                if ($targetVal === '' || !is_numeric($targetVal)) continue;

                $targetDate = sprintf('%s-%02d', $month, $day);
                if (!checkdate((int)substr($month, 5), $day, (int)substr($month, 0, 4))) continue;

                try {
                    WaRangkumanTarget::updateOrCreate(
                        ['Target_Date' => $targetDate, 'Category_Group' => $group, 'Category_Item' => $item],
                        ['Target' => (int)$targetVal, 'Updated_At' => now()]
                    );
                    $imported++;
                } catch (\Exception $e) {
                    $errors[] = "Baris " . ($ri + 1) . " Kolom $day: " . $e->getMessage();
                }
            }
        }

        WaRangkumanLog::create([
            'Action_Type' => 'IMPORT',
            'File_Name' => $file->getClientOriginalName(),
            'Total_Rows' => $imported,
            'Month' => $month,
            'Created_By' => session('Id_User'),
            'Created_At' => now(),
        ]);

        if (!empty($errors)) {
            return back()->with('warning', 'Import selesai dengan ' . count($errors) . ' error. ' . $imported . ' baris berhasil.');
        }
        return back()->with('success', 'Berhasil import ' . $imported . ' data target untuk ' . $month);
    }

    public function exportTarget(Request $request)
    {
        $month = $request->query('month', Carbon::now()->format('Y-m'));
        $year = (int)substr($month, 0, 4);
        $monthNum = (int)substr($month, 5);
        $daysInMonth = Carbon::createFromDate($year, $monthNum, 1)->daysInMonth;

        $targets = WaRangkumanTarget::whereYear('Target_Date', $year)
            ->whereMonth('Target_Date', $monthNum)
            ->get()
            ->keyBy(fn($t) => $t->Category_Group . '|' . $t->Category_Item . '|' . (int)substr($t->Target_Date, 8, 2));

        $categories = [
            ['TRANSMISI', 'SXG3 & SF'], ['TRANSMISI', 'Transmisi'],
            ['SUB ENGINE', 'Sub Engine'],
            ['LINE A', 'Unit'], ['LINE A', 'Mocol'],
            ['LINE B', 'Line B'],
            ['SUB ASSY', 'Sub Assy'],
            ['MAIN LINE', 'Mainline'],
            ['INSPEKSI', 'Inspeksi'],
            ['MOCOL', 'Unit'], ['MOCOL', 'Mower'], ['MOCOL', 'Collector'],
        ];

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Target ' . $month);

        $sheet->setCellValue('A1', 'Category Group');
        $sheet->setCellValue('B1', 'Category Item');
        for ($d = 1; $d <= $daysInMonth; $d++) {
            $col = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($d + 2);
            $sheet->setCellValue($col . '1', $d);
        }
        $lastCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($daysInMonth + 2);
        $this->applyPinkHeaderStyle($sheet, 'A1:' . $lastCol . '1');

        $rowNum = 2;
        foreach ($categories as $cat) {
            $sheet->setCellValue('A' . $rowNum, $cat[0]);
            $sheet->setCellValue('B' . $rowNum, $cat[1]);
            for ($d = 1; $d <= $daysInMonth; $d++) {
                $col = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($d + 2);
                $key = $cat[0] . '|' . $cat[1] . '|' . $d;
                $sheet->setCellValue($col . $rowNum, isset($targets[$key]) ? (int)$targets[$key]->Target : 0);
            }
            $this->applyTableBorder($sheet, 'A' . $rowNum . ':' . $lastCol . $rowNum);
            $rowNum++;
        }

        WaRangkumanLog::create([
            'Action_Type' => 'EXPORT',
            'File_Name' => 'wa_target_' . $month . '.xlsx',
            'Total_Rows' => count($categories),
            'Month' => $month,
            'Created_By' => session('Id_User'),
            'Created_At' => now(),
        ]);

        $fileName = 'WA_Target_' . $month . '.xlsx';
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $fileName . '"');
        header('Cache-Control: max-age=0');
        if (ob_get_level()) ob_end_clean();
        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit();
    }

    public function downloadTargetTemplate(Request $request)
    {
        $month = $request->query('month', Carbon::now()->format('Y-m'));
        $year = (int)substr($month, 0, 4);
        $monthNum = (int)substr($month, 5);
        $daysInMonth = Carbon::createFromDate($year, $monthNum, 1)->daysInMonth;

        $categories = [
            ['TRANSMISI', 'SXG3 & SF'], ['TRANSMISI', 'Transmisi'],
            ['SUB ENGINE', 'Sub Engine'],
            ['LINE A', 'Unit'], ['LINE A', 'Mocol'],
            ['LINE B', 'Line B'],
            ['SUB ASSY', 'Sub Assy'],
            ['MAIN LINE', 'Mainline'],
            ['INSPEKSI', 'Inspeksi'],
            ['MOCOL', 'Unit'], ['MOCOL', 'Mower'], ['MOCOL', 'Collector'],
        ];

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Target ' . $month);

        $sheet->setCellValue('A1', 'Category Group');
        $sheet->setCellValue('B1', 'Category Item');
        for ($d = 1; $d <= $daysInMonth; $d++) {
            $col = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($d + 2);
            $sheet->setCellValue($col . '1', $d);
        }
        $lastCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($daysInMonth + 2);
        $this->applyPinkHeaderStyle($sheet, 'A1:' . $lastCol . '1');

        $rowNum = 2;
        foreach ($categories as $cat) {
            $sheet->setCellValue('A' . $rowNum, $cat[0]);
            $sheet->setCellValue('B' . $rowNum, $cat[1]);
            for ($d = 1; $d <= $daysInMonth; $d++) {
                $col = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($d + 2);
                $sheet->setCellValue($col . $rowNum, 0);
            }
            $this->applyTableBorder($sheet, 'A' . $rowNum . ':' . $lastCol . $rowNum);
            $rowNum++;
        }

        $fileName = 'WA_Target_Template_' . $month . '.xlsx';
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $fileName . '"');
        header('Cache-Control: max-age=0');
        if (ob_get_level()) ob_end_clean();
        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit();
    }

    // ══════════════════════════════════════════════
    //  HISTORY & KOREKSI
    // ══════════════════════════════════════════════

    public function saveHistory(Request $request)
    {
        $date = $request->input('date');
        $rows = $request->input('rows');

        if (!$date || !$rows) {
            return response()->json(['success' => false, 'message' => 'Data tidak lengkap'], 400);
        }

        $inserted = 0;
        foreach ($rows as $group) {
            foreach ($group['items'] as $item) {
                WaRangkumanHistory::updateOrCreate(
                    [
                        'Log_Date' => $date,
                        'Category_Group' => $group['group'],
                        'Category_Item' => $item['label'],
                    ],
                    [
                        'Target' => $item['T'],
                        'Actual' => $item['A'],
                        'Selisih' => $item['S'],
                        'Grand_Total' => $item['GT'],
                        'Created_At' => now(),
                    ]
                );
                $inserted++;
            }
        }

        return response()->json([
            'success' => true,
            'message' => "Data untuk $date berhasil disimpan ($inserted item)",
            'inserted' => $inserted
        ]);
    }

    public function getHistory(Request $request)
    {
        $date = $request->input('date');
        $limit = $request->input('limit', 10);

        $query = WaRangkumanHistory::orderBy('Log_Date', 'desc')
            ->orderBy('Category_Group')
            ->orderBy('Id_History');

        if ($date) {
            $query->where('Log_Date', $date);
        }

        $history = $query->limit($limit)->get();

        // Kelompokkan berdasarkan tanggal
        $grouped = [];
        foreach ($history as $h) {
            $grouped[$h->Log_Date][] = $h;
        }

        return response()->json([
            'success' => true,
            'history' => $grouped,
        ]);
    }

    public function saveKoreksi(Request $request)
    {
        $date = $request->input('date');
        $rows = $request->input('rows');

        if (!$date) {
            return response()->json(['success' => false, 'message' => 'Tanggal tidak lengkap'], 400);
        }

        if ($rows) {
            foreach ($rows as $row) {
                $group = $row['group'] ?? '';
                $item = $row['item'] ?? '';
                $koreksi = $row['koreksi'] ?? '';

                if (!$group || !$item) continue;

                $newKor = $koreksi !== '' ? (int)$koreksi : null;

                // Cari record history yang sudah ada
                $existing = WaRangkumanHistory::where('Log_Date', $date)
                    ->where('Category_Group', $group)
                    ->where('Category_Item', $item)
                    ->first();

                if ($existing) {
                    // GT baru = GT_lama + (koreksi_baru - koreksi_lama)
                    $oldKor = $existing->Koreksi !== null && $existing->Koreksi !== '' ? (int)$existing->Koreksi : 0;
                    $newKorVal = $newKor ?? 0;
                    $adjustment = $newKorVal - $oldKor;
                    $newGT = (int)$existing->Grand_Total + $adjustment;

                    $existing->update([
                        'Koreksi' => $newKor,
                        'Grand_Total' => $newGT,
                    ]);
                } else {
                    // Belum ada history, create dengan Koreksi aja (GT tetap 0)
                    WaRangkumanHistory::create([
                        'Log_Date' => $date,
                        'Category_Group' => $group,
                        'Category_Item' => $item,
                        'Target' => 0,
                        'Actual' => 0,
                        'Selisih' => 0,
                        'Grand_Total' => $newKor ?? 0,
                        'Koreksi' => $newKor,
                        'Created_At' => now(),
                    ]);
                }
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Koreksi berhasil disimpan',
        ]);
    }

    // ══════════════════════════════════════════════
    //  WA MESSAGE & AUTO LOG
    // ══════════════════════════════════════════════

    // ══════════════════════════════════════════════
    //  MONTHLY DATA
    // ══════════════════════════════════════════════

    public function getHarianData(Request $request)
    {
        $date = $request->input('date', Carbon::today()->toDateString());
        $carbonDate = Carbon::parse($date);
        $monthStart = $carbonDate->copy()->startOfMonth();
        $monthEnd = $carbonDate->copy()->endOfMonth();
        $monthStartStr = $monthStart->toDateString();
        $monthEndStr = $monthEnd->toDateString();
        $daysInMonth = $carbonDate->daysInMonth;
        $monthLabel = $carbonDate->format('Y-m');
        $defaultDb = config('database.connections.mysql.database');

        // ── Helper: scan actual per day for an area (simple) ──
        $getScanActualsByDay = function ($areaId) use ($monthStartStr, $monthEndStr) {
            return Efficiency_Scan::where('Id_Area', $areaId)
                ->whereBetween('Time_Scan', [$monthStartStr, $monthEndStr . ' 23:59:59'])
                ->select(DB::raw('DATE(Time_Scan) as scan_date'), DB::raw('COUNT(DISTINCT Sequence_No_Plan) as cnt'))
                ->groupBy(DB::raw('DATE(Time_Scan)'))
                ->pluck('cnt', 'scan_date')
                ->toArray();
        };

        // ── Helper: scan actual per day with plan+tractor JOIN ──
        $getScanActualsByDayJoined = function ($areaId, $whereRaw) use ($monthStartStr, $monthEndStr, $defaultDb) {
            return Efficiency_Scan::from('scans')
                ->join(DB::connection('efficiency')->raw('tractors as t'), 'scans.Id_Tractor', '=', 't.Id_Tractor')
                ->leftJoin(DB::raw("`{$defaultDb}`.`plans` as plans"), function ($join) {
                    $join->on('scans.Sequence_No_Plan', '=', 'plans.Sequence_No_Plan')
                        ->on('scans.Production_Date_Plan', '=', 'plans.Production_Date_Plan');
                })
                ->where('scans.Id_Area', $areaId)
                ->whereBetween('scans.Time_Scan', [$monthStartStr, $monthEndStr . ' 23:59:59'])
                ->whereRaw($whereRaw)
                ->select(DB::raw('DATE(scans.Time_Scan) as scan_date'), DB::raw('COUNT(DISTINCT scans.Sequence_No_Plan) as cnt'))
                ->groupBy(DB::raw('DATE(scans.Time_Scan)'))
                ->pluck('cnt', 'scan_date')
                ->toArray();
        };

        // ── Helper: lineoff actual per day ──
        $getLineoffActualsByDay = function () use ($monthStartStr, $monthEndStr) {
            return Plan::whereNotNull('Lineoff_Plan')
                ->whereBetween('Lineoff_Plan', [$monthStartStr, $monthEndStr . ' 23:59:59'])
                ->select(DB::raw('DATE(Lineoff_Plan) as line_date'), DB::raw('COUNT(*) as cnt'))
                ->groupBy(DB::raw('DATE(Lineoff_Plan)'))
                ->pluck('cnt', 'line_date')
                ->toArray();
        };

        // ── Ambil actual scans per day untuk semua area ──
        $sxg3SfTypes = ["'SXG3'", "'SXG3MW'", "'SXG3日本'", "'SF2'", "'SF2 Trial'", "'SF2CL'", "'SF2CL日本'", "'SF2MW'", "'SF2MW日本'", "'SF2日本'", "'SF5'", "'SF5MW'"];
        $sxg3SfTypesStr = implode(',', $sxg3SfTypes);

        $actualsByDay = [
            'SUB ENGINE|Sub Engine'       => $getScanActualsByDay(6),
            'LINE B|Line B'               => $getScanActualsByDay(4),
            'SUB ASSY|Sub Assy'           => $getScanActualsByDay(7),
            'INSPEKSI|Inspeksi'           => $getScanActualsByDay(8),
            'TRANSMISI|SXG3 & SF'         => $getScanActualsByDayJoined(2, "(plans.Type_Plan IN ($sxg3SfTypesStr))"),
            'TRANSMISI|Transmisi'         => $getScanActualsByDayJoined(2, "(plans.Type_Plan IS NULL OR plans.Type_Plan NOT IN ($sxg3SfTypesStr))"),
            'LINE A|Unit'                 => $getScanActualsByDayJoined(3, "t.Name_Tractor = plans.Model_Name_Plan AND (plans.Model_Mower_Plan IS NULL OR t.Name_Tractor != plans.Model_Mower_Plan) AND (plans.Model_Collector_Plan IS NULL OR t.Name_Tractor != plans.Model_Collector_Plan)"),
            'LINE A|Mocol'                => $getScanActualsByDayJoined(3, "(t.Name_Tractor = plans.Model_Mower_Plan OR t.Name_Tractor = plans.Model_Collector_Plan)"),
            'MOCOL|Unit'                  => $getScanActualsByDayJoined(1, "scans.Sequence_No_Plan NOT REGEXP '[Tt]'"),
            'MOCOL|Mower'                 => $getScanActualsByDayJoined(1, "scans.Sequence_No_Plan REGEXP '[Tt]' AND plans.Model_Name_Plan = plans.Model_Mower_Plan"),
            'MOCOL|Collector'             => $getScanActualsByDayJoined(1, "scans.Sequence_No_Plan REGEXP '[Tt]' AND plans.Model_Name_Plan = plans.Model_Collector_Plan"),
        ];

        $lineoffActualsByDay = $getLineoffActualsByDay();

        // ── Query targets untuk bulan ini ──
        $targetRecords = WaRangkumanTarget::whereBetween('Target_Date', [$monthStartStr, $monthEndStr])
            ->get()
            ->groupBy(fn($t) => $t->Category_Group . '|' . $t->Category_Item . '|' . (int)substr($t->Target_Date, 8, 2));

        // ── Ambil history (override actual/target jika sudah disimpan) ──
        $historyRecords = WaRangkumanHistory::whereBetween('Log_Date', [$monthStartStr, $monthEndStr])
            ->get()
            ->keyBy(fn($h) => $h->Category_Group . '|' . $h->Category_Item . '|' . (int)Carbon::parse($h->Log_Date)->format('d'));

        $categories = [
            ['group' => 'TRANSMISI', 'items' => ['SXG3 & SF', 'Transmisi']],
            ['group' => 'SUB ENGINE', 'items' => ['Sub Engine']],
            ['group' => 'LINE A', 'items' => ['Unit', 'Mocol']],
            ['group' => 'LINE B', 'items' => ['Line B']],
            ['group' => 'SUB ASSY', 'items' => ['Sub Assy']],
            ['group' => 'MAIN LINE', 'items' => ['Mainline']],
            ['group' => 'INSPEKSI', 'items' => ['Inspeksi']],
            ['group' => 'MOCOL', 'items' => ['Unit', 'Mower', 'Collector']],
        ];

        $getDay = fn($day) => str_pad($day, 2, '0', STR_PAD_LEFT);

        $rows = [];
        foreach ($categories as $cat) {
            $itemRows = [];
            foreach ($cat['items'] as $item) {
                $key = $cat['group'] . '|' . $item;
                $dayActuals = [];
                $dayTargets = [];
                $dayHistory = [];

                for ($d = 1; $d <= $daysInMonth; $d++) {
                    $dayStr = $monthStart->format('Y-m') . '-' . $getDay($d);
                    $historyKey = $key . '|' . $d;

                    // Target: dari history dulu, baru dari wa_rangkuman_targets
                    $tVal = 0;
                    if (isset($historyRecords[$historyKey])) {
                        $tVal = (int)$historyRecords[$historyKey]->Target;
                    } else {
                        $targetKey = $key . '|' . $d;
                        $tVal = isset($targetRecords[$targetKey]) ? (int)$targetRecords[$targetKey]->first()->Target : 0;
                    }
                    $dayTargets[$d] = $tVal;

                    // Actual: dari history dulu (override), baru dari scans live
                    $fromHistory = false;
                    if (isset($historyRecords[$historyKey]) && (int)$historyRecords[$historyKey]->Actual > 0) {
                        $dayActuals[$d] = (int)$historyRecords[$historyKey]->Actual;
                        $fromHistory = true;
                    } elseif ($item === 'Mainline') {
                        $dayActuals[$d] = (int)($lineoffActualsByDay[$dayStr] ?? 0);
                    } else {
                        $dayActuals[$d] = (int)($actualsByDay[$key][$dayStr] ?? 0);
                    }
                    $dayHistory[$d] = $fromHistory;
                }

                $itemRows[] = [
                    'label' => $item,
                    'targets' => $dayTargets,
                    'actuals' => $dayActuals,
                    'history' => $dayHistory,
                ];
            }
            $rows[] = ['group' => $cat['group'], 'items' => $itemRows];
        }

        // Calculate monthly totals per item
        $totalTarget = 0;
        $totalActual = 0;
        foreach ($rows as &$group) {
            foreach ($group['items'] as &$item) {
                $item['monthT'] = array_sum($item['targets']);
                $item['monthA'] = array_sum($item['actuals']);
                $totalTarget += $item['monthT'];
                $totalActual += $item['monthA'];
            }
        }
        unset($item, $group);

        return response()->json([
            'success' => true,
            'rows' => $rows,
            'totals' => ['T' => $totalTarget, 'A' => $totalActual],
            'date' => $date,
            'month' => $monthLabel,
            'daysInMonth' => $daysInMonth,
            'monthName' => $carbonDate->locale('id')->isoFormat('MMMM YYYY'),
        ]);
    }

    // ══════════════════════════════════════════════
    //  EXPORT MONTHLY
    // ══════════════════════════════════════════════

    public function exportMonthly(Request $request)
    {
        $date = $request->input('date', Carbon::today()->toDateString());
        $carbonDate = Carbon::parse($date);
        $monthStart = $carbonDate->copy()->startOfMonth();
        $monthEnd = $carbonDate->copy()->endOfMonth();
        $monthStartStr = $monthStart->toDateString();
        $monthEndStr = $monthEnd->toDateString();
        $daysInMonth = $carbonDate->daysInMonth;
        $defaultDb = config('database.connections.mysql.database');

        $harianRequest = new Request(['date' => $date]);
        $harianResponse = $this->getHarianData($harianRequest);
        $data = json_decode($harianResponse->getContent(), true);

        if (!$data || !isset($data['rows'])) {
            abort(500, 'Gagal ambil data');
        }

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Produksi ' . $carbonDate->format('Y-m'));

        $sheet->setCellValue('A1', 'Category Group');
        $sheet->setCellValue('B1', 'Category Item');
        $sheet->setCellValue('C1', 'T/A');
        $colIdx = 4;
        for ($d = 1; $d <= $daysInMonth; $d++) {
            $col = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIdx);
            $sheet->setCellValue($col . '1', $d);
            $colIdx++;
        }
        $lastCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($daysInMonth + 3);
        $sheet->getStyle('A1:' . $lastCol . '1')->getFont()->setBold(true);
        $sheet->getStyle('A1:' . $lastCol . '1')->getAlignment()->setHorizontal('center');
        $sheet->getStyle('A1:' . $lastCol . '1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('FFC0CB');
        $this->applyTableBorder($sheet, 'A1:' . $lastCol . '1');

        $rowNum = 2;
        $styleCenter = $sheet->getStyle('A1')->getAlignment()->setVertical('center');

        foreach ($data['rows'] as $group) {
            $groupStartRow = $rowNum;
            $itemCount = count($group['items']);
            $groupRowCount = $itemCount * 2; // T + A per item

            foreach ($group['items'] as $item) {
                $itemStartRow = $rowNum;

                // ── T row ──
                $sheet->setCellValue('B' . $rowNum, $item['label']);
                $sheet->setCellValue('C' . $rowNum, 'T');
                $colIdx = 4;
                for ($d = 1; $d <= $daysInMonth; $d++) {
                    $col = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIdx);
                    $val = $item['targets'][$d] ?? 0;
                    $sheet->setCellValue($col . $rowNum, $val > 0 ? $val : '');
                    $colIdx++;
                }
                $sheet->getStyle('A' . $rowNum . ':' . $lastCol . $rowNum)->getFont()->setSize(10);
                $this->applyTableBorder($sheet, 'A' . $rowNum . ':' . $lastCol . $rowNum);
                $rowNum++;

                // ── A row ──
                $sheet->setCellValue('B' . $rowNum, '');
                $sheet->setCellValue('C' . $rowNum, 'A');
                $colIdx = 4;
                for ($d = 1; $d <= $daysInMonth; $d++) {
                    $col = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIdx);
                    $val = $item['actuals'][$d] ?? 0;
                    $tVal = $item['targets'][$d] ?? 0;
                    $sheet->setCellValue($col . $rowNum, $val > 0 ? $val : '');
                    if ($val > 0 && $tVal > 0) {
                        if ($val > $tVal) {
                            $sheet->getStyle($col . $rowNum)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('C6EFCE');
                        } elseif ($val < $tVal) {
                            $sheet->getStyle($col . $rowNum)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('FFC7CE');
                        }
                    }
                    $colIdx++;
                }
                $sheet->getStyle('A' . $rowNum . ':' . $lastCol . $rowNum)->getFont()->setSize(10);
                $this->applyTableBorder($sheet, 'A' . $rowNum . ':' . $lastCol . $rowNum);
                $rowNum++;

                // ── Merge Category Item (B) for T+A rows ──
                $sheet->mergeCells('B' . $itemStartRow . ':B' . ($rowNum - 1));
                $sheet->getStyle('B' . $itemStartRow)->getAlignment()->setVertical('center')->setHorizontal('left');
                $sheet->getStyle('B' . $itemStartRow)->getFont()->setBold(true)->setSize(10);
            }

            // ── Merge Category Group (A) for all rows in group ──
            $sheet->mergeCells('A' . $groupStartRow . ':A' . ($rowNum - 1));
            $sheet->setCellValue('A' . $groupStartRow, $group['group']);
            $sheet->getStyle('A' . $groupStartRow)->getAlignment()->setVertical('center')->setHorizontal('center');
            $sheet->getStyle('A' . $groupStartRow)->getFont()->setBold(true)->setSize(10);
        }

        // ── Styling ──
        $sheet->getColumnDimension('A')->setWidth(18);
        $sheet->getColumnDimension('B')->setWidth(15);
        $sheet->getColumnDimension('C')->setWidth(5);
        $sheet->getStyle('C1:C' . ($rowNum - 1))->getAlignment()->setHorizontal('center');
        for ($d = 1; $d <= $daysInMonth; $d++) {
            $col = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($d + 3);
            $sheet->getColumnDimension($col)->setWidth(5.5);
            $sheet->getStyle($col . ':' . $col)->getAlignment()->setHorizontal('center');
        }
        // Row height untuk merged cells
        for ($r = 2; $r <= $rowNum - 1; $r++) {
            $sheet->getRowDimension($r)->setRowHeight(18);
        }

        $fileName = 'Produksi_' . $carbonDate->format('Y_m') . '.xlsx';
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $fileName . '"');
        header('Cache-Control: max-age=0');
        if (ob_get_level()) ob_end_clean();
        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit();
    }

    // ══════════════════════════════════════════════
    //  EXCEL HELPERS
    // ══════════════════════════════════════════════

    private function applyPinkHeaderStyle($sheet, $range)
    {
        $style = $sheet->getStyle($range);
        $style->getFont()->setBold(true);
        $style->getAlignment()->setHorizontal('center');
        $style->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('FFC0CB');
        $style->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
    }

    private function applyTableBorder($sheet, $range)
    {
        $sheet->getStyle($range)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
    }
}
