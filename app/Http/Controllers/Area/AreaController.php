<?php

namespace App\Http\Controllers\Area;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Plan;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use App\Models\Type_User;
use App\Models\Efficiency_Area;
use App\Models\Efficiency_Scan;
use App\Models\Efficiency_Tractor;
use Yajra\DataTables\Facades\DataTables;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class AreaController extends Controller
{
    public function index(Request $request)
    {
        $areaName = session('Name_Area');
        $areaId = session('Id_Area');

        // Jika area adalah Main Line, tampilkan view mainline
        if ($areaName === 'MAIN LINE') {
            // Ambil tanggal dari query string, default ke hari ini
            $selectedDate = $request->query('lineoff_date', Carbon::today()->toDateString());

            $baseQuery = Plan::whereNotNull('Lineoff_Plan')
                            ->whereDate('Lineoff_Plan', $selectedDate);

            $totalTractors = $baseQuery->count();

            // Ambil tipe dan hitung jumlahnya per tipe untuk tanggal yang dipilih
            $typesWithCount = $baseQuery->select('Type_Plan', DB::raw('COUNT(*) as count'))
                                        ->groupBy('Type_Plan')
                                        ->orderBy('Type_Plan')
                                        ->get();
            
            return view('areas.mainline', compact('selectedDate', 'totalTractors', 'typesWithCount'));
        }

        // Untuk area lain, hitung Efficiency_Scan
        if (!$areaId) {
            return redirect()->route('login')->withErrors('Area ID tidak ditemukan dalam session');
        }

        // Ambil tanggal dari query string, default ke hari ini
        $selectedDate = $request->query('scan_date', Carbon::today()->toDateString());

        // Hitung scan untuk area ini dan tanggal yang dipilih
        $scanCount = Efficiency_Scan::where('Id_Area', $areaId)
                                    ->whereDate('Time_Scan', $selectedDate)
                                    ->count();

        return view('areas.index', compact('areaName', 'areaId', 'scanCount', 'selectedDate'));
    }

    public function getReports(Request $request)
    {
        $areaId = session('Id_Area');

        // Subquery dari tabel plans (default connection)
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

        // Query dari Efficiency_Scan dengan manual join
        $query = Efficiency_Scan::select(
            'scans.Id_Scan',
            '.Time_Scan',
            'scans.Assigned_Hour_Scan',
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
        )
        ->leftJoinSub(
            $plansSubquery,
            'plans',
            function($join) {
                $join->whereRaw('scans.Sequence_No_Plan = plans.Sequence_No_Plan')
                    ->on('scans.Production_Date_Plan', '=', 'plans.Production_Date_Plan');
            }
        )
        ->where('scans.Id_Area', $areaId);

        // Filter berdasarkan tanggal scan
        if ($request->filled('scan_date')) {
            $query->whereDate('scans.Time_Scan', $request->scan_date);
        } else {
            $query->whereDate('scans.Time_Scan', Carbon::today()->toDateString());
        }

        $query->orderBy('scans.Time_Scan', 'desc');

        return DataTables::of($query)
            ->addIndexColumn()
            ->make(true);
    }

    public function getMainlineReports(Request $request)
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
            'Lineoff_Plan'
        ])
            ->whereNotNull('Lineoff_Plan');

        if ($request->filled('lineoff_date')) {
            $query->whereDate('Lineoff_Plan', $request->lineoff_date);
        }

        $query->orderBy('Lineoff_Plan', 'desc');

        $results = $query->get();

        // Tambahkan Assigned_Hour_Scan dari Efficiency_Scan
        $results = $results->map(function($plan) {
            $scan = Efficiency_Scan::where(
                DB::connection('efficiency')->raw('LPAD(Sequence_No_Plan, 5, "0")'),
                '=',
                str_pad($plan->Sequence_No_Plan, 5, '0', STR_PAD_LEFT)
            )
            ->where('Production_Date_Plan', $plan->Production_Date_Plan)
            ->where('Id_Area', session('Id_Area'))
            ->orderBy('Time_Scan', 'desc')
            ->first();

            $plan->Assigned_Hour_Scan = $scan ? $scan->Assigned_Hour_Scan : '-';
            return $plan;
        });

        return DataTables::of($results)
            ->addIndexColumn()
            ->make(true);
    }

    public function scanMainlineStore(Request $request)
    {
        $request->validate([
            'sequence_no' => 'required|string|max:255',
            'production_date' => 'required',
        ]);

        $sequenceNo = $request->input('sequence_no');
        $productionDate = $request->input('production_date');
        $idArea = session('Id_Area'); // Ambil Id_Area dari session

        // Pastikan Id_Area tersedia
        if (!$idArea) {
            return redirect()->back()->with('error', 'Session area tidak ditemukan. Silakan login kembali.');
        }

        // Format sequence_no ke 5 digit dengan leading zero (jika belum oleh JS)
        $sequenceNoFormatted = str_pad($sequenceNo, 5, '0', STR_PAD_LEFT);

        $timestampNow = Carbon::now();

        try {
            // 1. Ambil Model_Name_Plan berdasarkan Sequence_No_Plan dan Production_Date_Plan
            $plan = DB::table('plans')
                ->where('Sequence_No_Plan', $sequenceNoFormatted)
                ->where('Production_Date_Plan', $productionDate)
                ->first();

            if (!$plan) {
                return redirect()->back()->with('error', "Plan dengan Sequence_No_Plan '{$sequenceNoFormatted}' dan Production_Date_Plan '{$productionDate}' tidak ditemukan di database PODIUM.");
            }

            $modelName = $plan->Model_Name_Plan;

            // 2. Cari Tractor di database EFFICIENCY berdasarkan Name_Tractor = Model_Name_Plan
            $tractor = Efficiency_Tractor::where('Name_Tractor', $modelName)->first();

            if (!$tractor) {
                return redirect()->back()->with('error', 'Jam Tractor belum diinputkan.');
            }

            // 3. Update or Create ke table Efficiency_Scan berdasarkan kriteria unik
            // Kita asumsikan kombinasi Id_Area, Sequence_No_Plan, dan Production_Date_Plan adalah unik untuk satu scan
            $uniqueKey = [
                'Id_Area' => $idArea,
                'Sequence_No_Plan' => $sequenceNoFormatted,
                'Production_Date_Plan' => $productionDate,
                // Tambahkan field lain jika diperlukan untuk membuat kriteria unik lebih spesifik
                // Misalnya, jika satu sequence bisa discan beberapa kali per hari, tambahkan Id_Tractor
                // 'Id_Tractor' => $tractor->Id_Tractor,
            ];

            $scanData = [
                'Id_Tractor' => $tractor->Id_Tractor,
                'Time_Scan' => $timestampNow,
                'Assigned_Hour_Scan' => $tractor->Hour_Tractor,
                // 'Sequence_No_Plan' => $sequenceNoFormatted, // Sudah ada di $uniqueKey
                // 'Production_Date_Plan' => $productionDate, // Sudah ada di $uniqueKey
                // 'Id_Area' => $idArea, // Sudah ada di $uniqueKey
                // 'Id_Member' dan 'Id_Daily_Job' bisa diisi jika tersedia di session atau request
                // 'Nik_Replace' bisa diisi jika tersedia
            ];

            Efficiency_Scan::updateOrCreate($uniqueKey, $scanData);

            // 4. Update kolom Lineoff_Plan di tabel plans (di database PODIUM)
            $updatedRows = DB::table('plans')
                ->where('Sequence_No_Plan', $sequenceNoFormatted)
                ->where('Production_Date_Plan', $productionDate)
                ->update([
                    'Lineoff_Plan' => $timestampNow
                ]);

            if ($updatedRows === 0) {
                // Harusnya tidak terjadi jika plan ditemukan di awal, tapi jaga-jaga
                return redirect()->back()->with('error', "Gagal memperbarui Lineoff_Plan untuk Plan dengan Sequence_No_Plan '{$sequenceNoFormatted}'.");
            }

            // --- SISA LOGIKA UPDATE STATUS PLAN (TETAP SAMA) ---
            // Ambil plan yang baru diupdate untuk pengecekan status
            $updatedPlan = DB::table('plans')
                ->where('Sequence_No_Plan', $sequenceNoFormatted)
                ->where('Production_Date_Plan', $productionDate)
                ->first();

            if (!$updatedPlan) {
                return redirect()->back()->with('error', 'Terjadi kesalahan internal saat membaca plan setelah update.');
            }

            $updatedModelName = $updatedPlan->Model_Name_Plan;

            $rule = DB::table('rules')->where('Type_Rule', $updatedModelName)->first();
            if (!$rule) {
                return redirect()->back()->with('success', "Data Lineoff untuk Sequence No {$sequenceNoFormatted} berhasil diperbarui dan dicatat di Efficiency, tetapi rule tidak ditemukan untuk menentukan status Plan.");
            }

            $ruleSequenceRaw = $rule->Rule_Rule;
            $recordPlanRaw = $updatedPlan->Record_Plan;
            $ruleSequence = null;
            $recordPlan = [];

            if (is_string($ruleSequenceRaw) && !empty($ruleSequenceRaw)) {
                $decodedRule = json_decode($ruleSequenceRaw, true);
                if (is_array($decodedRule)) {
                    $ruleSequence = $decodedRule;
                } else {
                    return redirect()->back()->with('error', 'Format rule untuk model ini rusak.');
                }
            } else {
                DB::table('plans')
                    ->where('Sequence_No_Plan', $sequenceNoFormatted)
                    ->where('Production_Date_Plan', $productionDate)
                    ->update(['Status_Plan' => 'done']);
                return redirect()->back()->with('success', "Data Lineoff untuk Sequence No {$sequenceNoFormatted} berhasil diperbarui dan dicatat di Efficiency. Status Plan: Done (Tidak ada rule).");
            }

            if (is_string($recordPlanRaw) && !empty($recordPlanRaw)) {
                $decodedRecord = json_decode($recordPlanRaw, true);
                if (is_array($decodedRecord)) {
                    $recordPlan = $decodedRecord;
                } else {
                    return redirect()->back()->with('error', 'Format record plan untuk plan ini rusak.');
                }
            }

            $allProcessesCompleted = true;
            foreach ($ruleSequence as $processName) {
                if (!isset($recordPlan[$processName])) {
                    $allProcessesCompleted = false;
                    break;
                }
            }

            $newStatus = $allProcessesCompleted ? 'done' : 'pending';

            DB::table('plans')
                ->where('Sequence_No_Plan', $sequenceNoFormatted)
                ->where('Production_Date_Plan', $productionDate)
                ->update(['Status_Plan' => $newStatus]);

            $statusMessage = $allProcessesCompleted ? " Dan Status Plan: Done." : "";

            // Redirect ke route 'home' setelah sukses
            return redirect()->route('home')->with('success', "Data Lineoff untuk Sequence No {$sequenceNoFormatted} berhasil diperbarui dan dicatat di Efficiency." . $statusMessage);

        } catch (\Exception $e) {
            \Log::error('Gagal memproses scanMainlineStore: ' . $e->getMessage(), ['exception' => $e]);
            return redirect()->back()->with('error', 'Gagal memproses data Lineoff dan Efficiency Scan: ' . $e->getMessage());
        }
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

    public function exportReport(Request $request)
    {
        $request->validate([
            'lineoff_date' => 'required|date_format:Y-m-d',
        ]);

        $selectedDate = Carbon::parse($request->query('lineoff_date'))->startOfDay();
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
        $sheet->setCellValue('C' . $currentRow, session('Name_Area'));
        $this->applyPinkCellStyle($sheet, 'B' . $currentRow . ':C' . $currentRow); // Apply pink style
        $this->applyTableBorder($sheet, 'B' . $currentRow . ':C' . $currentRow);
        $currentRow++;

        // 1. Judul Tanggal (Pink Cell)
        $sheet->setCellValue('B' . $currentRow, 'Tanggal Scan:');
        $sheet->setCellValue('C' . $currentRow, $selectedDate->format('d F Y'));
        $this->applyPinkCellStyle($sheet, 'B' . $currentRow . ':C' . $currentRow); // Apply pink style
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
        // --- STYLING UNTUK TOTAL DATA (Pink Cell dengan Font Besar/Bold) ---
        $style = $sheet->getStyle('C' . $currentRow);
        $style->getFont()->setSize(14)->setBold(true);
        $this->applyTableBorder($sheet, 'B' . $currentRow . ':C' . $currentRow);
        // --- AKHIR STYLING ---
        $currentRow += 2; // Loncat ke baris ke-5 untuk header tabel

        // 4. Header Tabel Data (Kolom A ke L) - Baris ke-5 (Pink Header)
        $headers = [
            'No', 'Sequence No', 'Model Name', 'Type', 'Production No', 'Production Date', 'Scan', 'Chasis No',
            'Model Label', 'Safety Frame Label', 'Model Mower', 'Mower No', 'Model Collector', 'Collector No'
        ];
        $colIndex = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($colIndex . $currentRow, $header);
            $colIndex++;
        }
        $tableHeaderRow = $currentRow; // Simpan nomor baris header tabel
        $this->applyPinkHeaderStyle($sheet, 'A' . $tableHeaderRow . ':N' . $tableHeaderRow); // Apply pink header style
        $currentRow++; // Pindah ke baris data pertama

        // 5. Isi Data Tabel (Kolom A ke L)
        $no = 1;
        foreach ($plans as $plan) {
            $colIndex = 'A';
            $sheet->setCellValue($colIndex . $currentRow, $no); $sheet->getStyle($colIndex . $currentRow)->getAlignment()->setHorizontal('center'); $colIndex++;
            $sheet->setCellValue($colIndex . $currentRow, $plan->Sequence_No_Plan); $sheet->getStyle($colIndex . $currentRow)->getAlignment()->setHorizontal('left'); $colIndex++;
            $sheet->setCellValue($colIndex . $currentRow, $plan->Model_Name_Plan); $sheet->getStyle($colIndex . $currentRow)->getAlignment()->setHorizontal('left'); $colIndex++;
            $sheet->setCellValue($colIndex . $currentRow, $plan->Type_Plan); $sheet->getStyle($colIndex . $currentRow)->getAlignment()->setHorizontal('left'); $colIndex++;
            $sheet->setCellValue($colIndex . $currentRow, $plan->Production_No_Plan); $sheet->getStyle($colIndex . $currentRow)->getAlignment()->setHorizontal('left'); $colIndex++;
            $sheet->setCellValue($colIndex . $currentRow, $plan->Production_Date_Plan); $sheet->getStyle($colIndex . $currentRow)->getAlignment()->setHorizontal('left'); $colIndex++;
            $sheet->setCellValue($colIndex . $currentRow, $plan->Lineoff_Plan); $sheet->getStyle($colIndex . $currentRow)->getAlignment()->setHorizontal('left'); $colIndex++;
            $sheet->setCellValue($colIndex . $currentRow, $plan->Chasis_No_Plan); $sheet->getStyle($colIndex . $currentRow)->getAlignment()->setHorizontal('left'); $colIndex++;
            $sheet->setCellValue($colIndex . $currentRow, $plan->Model_Label_Plan); $sheet->getStyle($colIndex . $currentRow)->getAlignment()->setHorizontal('left'); $colIndex++;
            $sheet->setCellValue($colIndex . $currentRow, $plan->Safety_Frame_Label_Plan); $sheet->getStyle($colIndex . $currentRow)->getAlignment()->setHorizontal('left'); $colIndex++;
            $sheet->setCellValue($colIndex . $currentRow, $plan->Model_Mower_Plan); $sheet->getStyle($colIndex . $currentRow)->getAlignment()->setHorizontal('left'); $colIndex++;
            $sheet->setCellValue($colIndex . $currentRow, $plan->Mower_No_Plan); $sheet->getStyle($colIndex . $currentRow)->getAlignment()->setHorizontal('left'); $colIndex++;
            $sheet->setCellValue($colIndex . $currentRow, $plan->Model_Collector_Plan); $sheet->getStyle($colIndex . $currentRow)->getAlignment()->setHorizontal('left'); $colIndex++;
            $sheet->setCellValue($colIndex . $currentRow, $plan->Collector_No_Plan); $sheet->getStyle($colIndex . $currentRow)->getAlignment()->setHorizontal('left'); $colIndex++;
            $no++;
            $currentRow++;
        }
        $lastDataRow = $currentRow - 1; // Simpan nomor baris data terakhir

        // --- STYLING TABEL DATA ---
        // Border untuk seluruh tabel data
        $this->applyTableBorder($sheet, 'A' . $tableHeaderRow . ':N' . $lastDataRow);

        // Filter Otomatis
        $sheet->setAutoFilter('A' . $tableHeaderRow . ':N' . $lastDataRow);

        // Auto-size kolom A ke N (tetap aktif)
        foreach (range('A', 'N') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
        // --- AKHIR STYLING TABEL DATA ---

        // 6. Kolom N: Header Rekap Tipe (Baris ke-5, Pink Header)
        $rekapHeaderRow = 5; // Mulai dari baris yang sama dengan header tabel
        $sheet->setCellValue('P' . $rekapHeaderRow, 'Type:');
        $this->applyPinkHeaderStyle($sheet, 'P' . $rekapHeaderRow . ':Q' . $rekapHeaderRow); // Apply pink header style

        // 7. Kolom N & O: Isi Rekap Tipe & Jumlah (Dimulai dari baris ke-6)
        $currentRekapRow = $rekapHeaderRow + 1; // Baris pertama data rekap
        foreach ($sortedTypeCounts as $type => $count) {
            $sheet->setCellValue('P' . $currentRekapRow, $type);
            $sheet->setCellValue('Q' . $currentRekapRow, $count);
            $currentRekapRow++;
        }

        // --- STYLING REKAP TIPE ---
        // Border untuk seluruh data rekap
        $this->applyTableBorder($sheet, 'P' . ($rekapHeaderRow + 1) . ':Q' . ($currentRekapRow - 1)); // Dari baris data pertama hingga terakhir

        // Total di bawah rekap (misalnya di baris $currentRekapRow)
        // Ganti 'Total Tipe:' menjadi 'Total Keseluruhan:' atau sesuaikan
        $sheet->setCellValue('P' . $currentRekapRow, 'Total Keseluruhan:'); 
        // Gunakan $totalPlans yang dihitung di awal fungsi
        $sheet->setCellValue('Q' . $currentRekapRow, $totalPlans); 
        // Styling Total Rekap (Pink Cell)
        $style = $sheet->getStyle('P' . $currentRekapRow . ':Q' . $currentRekapRow);
        $style->getFont()->setBold(true);
        $style->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('FFC0CB'); // Pink muda
        $style->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

        // Auto-size kolom P & Q (tetap aktif)
        foreach (range('P', 'Q') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
        // --- AKHIR STYLING REKAP TIPE ---

        // --- OUTPUT KE BROWSER ---
        $fileName = 'Report_' . $selectedDate->format('Y-m-d') . '.xlsx';

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
}
