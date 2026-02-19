<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Type_User;
use App\Models\Plan;
use App\Models\Efficiency_Area;
use App\Models\Efficiency_Scan;
use Yajra\DataTables\Facades\DataTables;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class MainController extends Controller
{

    public function index()
    {
        $areas = Efficiency_Area::all();

        if (session()->has('Id_User')) {
            $user = User::find(session('Id_User'));
            if ($user->Id_Type_User == 2) {
                return redirect()->route('dashboard');
            } elseif ($user->Id_Type_User == 1) {
                return redirect()->route('home');
            }
        }
        if (session()->has('Id_Area')) {
            return redirect()->route('home');
        }

        return view('auth.login', compact('areas'));
    }

    public function login(Request $request)
    {
        $request->validate([
            'Username_User' => 'required',
            'Password_User' => 'required'
        ]);

        // Cari user berdasarkan username
        $user = User::where('Username_User', $request->Username_User)->first();

        // Verifikasi password (plain text, karena kamu tidak pakai hashing)
        if ($user && $request->Password_User === $user->Password_User) {
            Auth::login($user); // ✅ Ini yang membuat Laravel tahu kamu login

            session(['Id_User' => $user->Id_User]);
            session(['Id_Type_User' => $user->Id_Type_User]);
            session(['Username_User' => $user->Username_User]);

            if ($user->Id_Type_User == 2) {
                return redirect()->route('dashboard');
            } else if ($user->Id_Type_User == 1) {
                return redirect()->route('home');
            }
        }

        return back()->withErrors(['loginError' => 'Invalid username or password']);
    }

    public function login_area(Request $request)
    {
        $request->validate([
            'Name_Area' => 'required',
            'Password_Area' => 'required'
        ]);

        // Cek jika login DAIICHI (hardcoded)
        if ($request->Name_Area === 'DAIICHI' && $request->Password_Area === 'daiichi123') {
            session(['Id_Area' => 999]); // ID khusus untuk DAIICHI
            session(['Name_Area' => 'DAIICHI']);
            return redirect()->route('home');
        }

        // Cek jika login DAISHA SET (hardcoded)
        if ($request->Name_Area === 'DAISHA SET' && $request->Password_Area === 'daishaset123') {
            session(['Id_Area' => 888]); // ID khusus untuk DAISHA SET
            session(['Name_Area' => 'DAISHA SET']);
            return redirect()->route('home');
        }

        // Cari area berdasarkan nama area (untuk area lain)
        $area = Efficiency_Area::where('Name_Area', $request->Name_Area)->first();

        // Verifikasi password (plain text, karena kamu tidak pakai hashing)
        if ($area && $request->Password_Area === $area->Password_Area) {
            session(['Id_Area' => $area->Id_Area]);
            session(['Name_Area' => $area->Name_Area]);
            return redirect()->route('home');
        }

        return back()->withErrors(['loginError' => 'Invalid area name or password']);
    }

    public function logout()
    {
        Auth::logout();

        session()->forget('Id_User');
        session()->forget('Id_Type_User');
        session()->forget('Username_User');
        
        return redirect()->route('/');
    }

    public function logout_area()
    {
        Auth::logout();

        session()->forget('Id_Area');
        session()->forget('Name_Area');

        return redirect()->route('/');
    }

    public function admin(){
        $type_user = Type_User::all();
        return view('admin', compact('type_user'));
    }

    public function create(Request $request){
        // melakukan validasi data
        $request->validate([
            'Name_User' => 'required',
            'Username_User' => 'required|unique:users,Username_User',
            'Password_User' => 'required',
            'Id_Type_User' => 'required',
            'Id_Area' => 'required'
        ],
        [
            'Name_User.required' => 'Nama wajib diisi',
            'Username_User.required' => 'Username wajib diisi',
            'Username_User.unique' => 'Username sudah digunakan, pilih yang lain',
            'Password_User.required' => 'Password wajib diisi',
            'Id_Type_User.required' => 'Type User wajib diisi',
            'Id_Area.required' => 'Area wajib diisi'
        ]);
        
        //tambah data user
        DB::table('users')->insert([
            'Name_User' => $request->input('Name_User'),
            'Username_User' => $request->input('Username_User'),
            'Password_User' => $request->input('Password_User'),
            'Id_Type_User' => $request->input('Id_Type_User'),
            'Id_Area' => $request->input('Id_Area')
        ]);
        
        return redirect()->route('login');
    }

    public function scan(Request $request)
    {
        // Ambil tanggal dari query string, default ke hari ini
        $selectedDate = $request->query('lineoff_date', Carbon::today()->toDateString());

        $baseQuery = Plan::whereNotNull('Lineoff_Plan')
                        ->whereDate('Lineoff_Plan', $selectedDate); // Filter langsung ke tanggal tertentu

        $totalTractors = $baseQuery->count();

        // Ambil tipe dan hitung jumlahnya per tipe untuk tanggal yang dipilih
        $typesWithCount = $baseQuery->select('Type_Plan', DB::raw('COUNT(*) as count'))
                                    ->groupBy('Type_Plan')
                                    ->orderBy('Type_Plan') // Urutkan berdasarkan tipe
                                    ->get();

        return view('lineoff.scan', compact('selectedDate', 'totalTractors', 'typesWithCount'));
    }

    public function scanStore(Request $request)
    {
        $request->validate([
            'sequence_no' => 'required|string|max:255',
            'production_date' => 'required',
        ]);

        $sequenceNo = $request->input('sequence_no');
        $productionDate = $request->input('production_date');

        // Format sequence_no ke 5 digit dengan leading zero (jika belum oleh JS)
        $sequenceNoFormatted = str_pad($sequenceNo, 5, '0', STR_PAD_LEFT);

        $timestampNow = Carbon::now();

        try {
            // 1. Update kolom Lineoff_Plan di tabel plans
            $updatedRows = DB::table('plans')
                ->where('Sequence_No_Plan', $sequenceNoFormatted)
                ->where('Production_Date_Plan', $productionDate)
                ->update([
                    'Lineoff_Plan' => $timestampNow
                ]);

            if ($updatedRows === 0) {
                return redirect()->back()->withErrors(['sequence_no' => "Plan dengan Sequence_No_Plan '{$sequenceNoFormatted}' tidak ditemukan."]);
            }

            // 2. Ambil plan yang baru diupdate untuk pengecekan status
            $plan = DB::table('plans')
                ->where('Sequence_No_Plan', $sequenceNoFormatted)
                ->where('Production_Date_Plan', $productionDate)
                ->first();

            if (!$plan) {
                // Harusnya tidak terjadi jika update berhasil, tapi jaga-jaga
                return redirect()->back()->withErrors(['general' => 'Terjadi kesalahan internal.']);
            }

            // 3. Ambil Model_Name_Plan untuk mencari rule
            $modelName = $plan->Model_Name_Plan;

            // 4. Ambil rule berdasarkan Type_Rule (Model_Name_Plan)
            $rule = DB::table('rules')
                ->where('Type_Rule', $modelName)
                ->first();

            if (!$rule) {
                // Jika rule tidak ditemukan, tidak bisa menentukan status, biarkan kosong atau isi pesan error jika diperlukan
                // Di sini, kita asumsikan status tetap kosong jika rule tidak ada
                return redirect()->back()->with('success', "Data Lineoff untuk Sequence No {$sequenceNoFormatted} berhasil diperbarui, tetapi rule tidak ditemukan untuk menentukan status.");
            }

            // 5. Decode Rule_Rule dan Record_Plan
            $ruleSequenceRaw = $rule->Rule_Rule; // String JSON
            $recordPlanRaw = $plan->Record_Plan;  // String JSON

            $ruleSequence = null;
            $recordPlan = [];

            // Decode Rule_Rule
            if (is_string($ruleSequenceRaw) && !empty($ruleSequenceRaw)) {
                $decodedRule = json_decode($ruleSequenceRaw, true);
                if (is_array($decodedRule)) {
                    $ruleSequence = $decodedRule;
                } else {
                    return redirect()->back()->withErrors(['general' => 'Format rule untuk model ini rusak.']);
                }
            } else {
                // Jika rule kosong, kita asumsikan tidak ada proses, jadi statusnya 'done' atau 'not_applicable'
                // Di sini kita anggap 'done' karena tidak ada yang harus diselesaikan
                DB::table('plans')
                    ->where('Sequence_No_Plan', $sequenceNoFormatted)
                    ->where('Production_Date_Plan', $productionDate)
                    ->update(['Status_Plan' => 'done']);
                return redirect()->back()->with('success', "Data Lineoff untuk Sequence No {$sequenceNoFormatted} berhasil diperbarui. Status Plan: Done (Tidak ada rule).");
            }

            // Decode Record_Plan
            if (is_string($recordPlanRaw) && !empty($recordPlanRaw)) {
                $decodedRecord = json_decode($recordPlanRaw, true);
                if (is_array($decodedRecord)) {
                    $recordPlan = $decodedRecord;
                } else {
                    return redirect()->back()->withErrors(['general' => 'Format record plan untuk plan ini rusak.']);
                }
            } // Jika recordPlanRaw kosong/null, biarkan $recordPlan sebagai array kosong []

            // 6. Cek apakah semua proses dalam rule sudah ada di recordPlan
            $allProcessesCompleted = true;
            foreach ($ruleSequence as $processName) {
                if (!isset($recordPlan[$processName])) {
                    $allProcessesCompleted = false;
                    break; // Cukup satu yang belum selesai, langsung berhenti
                }
            }

            // 7. Tentukan status dan update kolom Status_Plan
            $newStatus = $allProcessesCompleted ? 'done' : 'pending'; // Atau status lain sesuai kebutuhan

            DB::table('plans')
                ->where('Sequence_No_Plan', $sequenceNoFormatted)
                ->where('Production_Date_Plan', $productionDate)
                ->update(['Status_Plan' => $newStatus]);

            $statusMessage = $allProcessesCompleted ? " Dan Status Plan: Done." : "";

            // Redirect ke route 'lineoff' setelah sukses
            return redirect()->route('scan')->with('success', "Data Lineoff untuk Sequence No {$sequenceNoFormatted} berhasil diperbarui." . $statusMessage);

        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['general' => 'Gagal memperbarui data Lineoff dan Status: ' . $e->getMessage()]);
        }
    }

    public function lineoff(Request $request)
    {
        // Ambil tanggal dari query string, default ke hari ini
        $selectedDate = $request->query('lineoff_date', Carbon::today()->toDateString());

        $baseQuery = Plan::whereNotNull('Lineoff_Plan')
                        ->whereDate('Lineoff_Plan', $selectedDate); // Filter langsung ke tanggal tertentu

        $totalTractors = $baseQuery->count();

        // Ambil tipe dan hitung jumlahnya per tipe untuk tanggal yang dipilih
        $typesWithCount = $baseQuery->select('Type_Plan', DB::raw('COUNT(*) as count'))
                                    ->groupBy('Type_Plan')
                                    ->orderBy('Type_Plan') // Urutkan berdasarkan tipe
                                    ->get();

        return view('lineoff.list', compact('selectedDate', 'totalTractors', 'typesWithCount'));
    }

    public function getLineoff(Request $request)
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

        return DataTables::of($query)
            ->addIndexColumn()
            ->make(true);
    }

    public function report(Request $request)
    {
        $sub = "area";

        // Ambil semua area dari Efficiency_Area
        $areas = Efficiency_Area::select('Id_Area', 'Name_Area')
            ->orderBy('Name_Area')
            ->get();

        return view('lineoff.report', compact('sub', 'areas'));
    }

    public function getReports(Request $request)
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
            // Gunakan logika MAIN LINE - ambil dari tabel Plan berdasarkan Lineoff_Plan
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

    public function exportLineoff(Request $request)
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

        // 1. Judul Tanggal (Pink Cell)
        $sheet->setCellValue('B' . $currentRow, 'Tanggal Lineoff:');
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
            'No', 'Sequence No', 'Model Name', 'Type', 'Production No', 'Production Date', 'Lineoff', 'Chasis No',
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
        $fileName = 'Lineoff_' . $selectedDate->format('Y-m-d') . '.xlsx';

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