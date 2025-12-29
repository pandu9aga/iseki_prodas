<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Plan;
use App\Models\Rule;
use Yajra\DataTables\Facades\DataTables;

class PlanController extends Controller
{
    public function index(){
        $page = "plan";

        $Id_User = session('Id_User');
        $user = User::find($Id_User);

        return view('admins.plans.index', compact('page', 'user'));
    }

    public function getPlans(Request $request) // Tambahkan $request
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
            'Collector_No_Plan'
        ]);

        // --- TAMBAHAN: Filter berdasarkan tahun ---
        $tahun = $request->input('tahun'); // Ambil dari request DataTables
        if ($tahun) {
            // Konversi tahun ke format yang sesuai untuk pencocokan di database
            // Kita ingin mencocokkan Production_Date_Plan yang tahunnya sesuai
            // Misalnya, jika $tahun = 2025, kita cari Production_Date_Plan antara 20250101 dan 20251231
            $startOfYear = (int)($tahun . '0101'); // 20250101
            $endOfYear = (int)($tahun . '1231');   // 20251231

            $query->whereBetween('Production_Date_Plan', [$startOfYear, $endOfYear]);
        } else {
            // Jika tidak ada filter tahun, gunakan tahun saat ini sebagai default
            $currentYear = Carbon::now()->year;
            $startOfYear = (int)($currentYear . '0101');
            $endOfYear = (int)($currentYear . '1231');
            $query->whereBetween('Production_Date_Plan', [$startOfYear, $endOfYear]);
        }
        // --- AKHIR TAMBAHAN ---

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('action', function ($row) {
                return '
                    <a href="'.route('plan.edit', $row->Id_Plan).'" class="btn btn-sm btn-outline-primary">
                        <span class="tf-icons bx bx-edit"></span>
                    </a>
                    <button class="btn btn-sm btn-outline-danger delete-btn"
                            data-id="'.$row->Id_Plan.'"
                            data-name="'.$row->Sequence_No_Plan.'">
                        <span class="tf-icons bx bx-trash"></span>
                    </button>
                ';
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function add()
    {
        $page = "plan";

        $Id_User = session('Id_User');
        $user = User::find($Id_User);

        return view('admins.plans.add', compact('page', 'user'));
    }

    public function create(Request $request)
    {
        // Simpan semua data dari form, karena semuanya optional
        $plan = new Plan();
        $plan->Type_Plan = $request->input('Type_Plan');
        $plan->Sequence_No_Plan = $request->input('Sequence_No_Plan');
        $plan->Production_Date_Plan = $request->input('Production_Date_Plan');
        $plan->Model_Name_Plan = $request->input('Model_Name_Plan');
        $plan->Production_No_Plan = $request->input('Production_No_Plan');
        $plan->Chasis_No_Plan = $request->input('Chasis_No_Plan');
        $plan->Model_Label_Plan = $request->input('Model_Label_Plan');
        $plan->Safety_Frame_Label_Plan = $request->input('Safety_Frame_Label_Plan');
        $plan->Model_Mower_Plan = $request->input('Model_Mower_Plan');
        $plan->Mower_No_Plan = $request->input('Mower_No_Plan');
        $plan->Model_Collector_Plan = $request->input('Model_Collector_Plan');
        $plan->Collector_No_Plan = $request->input('Collector_No_Plan');
        $plan->save();
        
        return redirect()->route('plan');
    }

    public function edit(Plan $Id_Plan)
    {
        $page = "plan";

        $Id_User_Session = session('Id_User');
        $user = User::find($Id_User_Session);

        return view('admins.plans.edit', compact('page', 'user', 'Id_Plan'));
    }

    public function update(Request $request, $Id_Plan)
    {
        // validasi input
        $validated = $request->validate([
            'Type_Plan'                => 'nullable|max:255',
            'Sequence_No_Plan'         => 'nullable|max:255',
            'Production_Date_Plan'     => 'nullable|max:255',
            'Model_Name_Plan'          => 'nullable|max:255',
            'Production_No_Plan'       => 'nullable|max:255',
            'Chasis_No_Plan'           => 'nullable|max:255',
            'Model_Label_Plan'         => 'nullable|max:255',
            'Safety_Frame_Label_Plan'  => 'nullable|max:255',
            'Model_Mower_Plan'         => 'nullable|max:255',
            'Mower_No_Plan'            => 'nullable|max:255',
            'Model_Collector_Plan'     => 'nullable|max:255',
            'Collector_No_Plan'        => 'nullable|max:255',
        ]);

        // ambil data plan berdasarkan Id_Plan
        $plan = Plan::findOrFail($Id_Plan);

        // update data plan
        $plan->update($validated);
                
        return redirect()->route('plan');
    }

    public function destroy($Id_Plan) // jangan pakai Plan $Id_Plan, karena AJAX
    {
        $plan = Plan::findOrFail($Id_Plan);
        $plan->delete();
        
        return response()->json(['success' => true, 'message' => 'Data delete successfully']);
    }

    public function import(Request $request)
    {
        $request->validate([
            'excel' => 'required|file|mimes:xlsx,xls'
        ]);

        ini_set('max_execution_time', 600);
        ini_set('memory_limit', '512M');

        $file = $request->file('excel');
        $spreadsheet = IOFactory::load($file->getPathname());
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray();

        $inserted = 0;
        $updated = 0;

        // Anggap baris pertama adalah header, maka skip dengan array_slice
        foreach (array_slice($rows, 1) as $row) {
            if (count($row) >= 12) {
                $sequenceNo = trim($row[1]); // kolom Sequence_No_Plan
                $productionDate = trim($row[2]); // kolom Production_Date_Plan

                $data = [
                    'Type_Plan'               => $row[0] ?? null,
                    'Sequence_No_Plan'        => $row[1] ?? null,
                    'Production_Date_Plan'    => $row[2] ?? null,
                    'Model_Name_Plan'         => $row[3] ?? null,
                    'Production_No_Plan'      => $row[4] ?? null,
                    'Chasis_No_Plan'          => $row[5] ?? null,
                    'Model_Label_Plan'        => $row[6] ?? null,
                    'Safety_Frame_Label_Plan' => $row[7] ?? null,
                    'Model_Mower_Plan'        => $row[8] ?? null,
                    'Mower_No_Plan'           => $row[9] ?? null,
                    'Model_Collector_Plan'    => $row[10] ?? null,
                    'Collector_No_Plan'       => $row[11] ?? null,
                ];

                if (!empty($sequenceNo)) {
                    // cek apakah sequenceNo sudah ada
                    $exists = DB::table('plans')
                                    ->where('Sequence_No_Plan', $sequenceNo)
                                    ->where('Production_Date_Plan', $productionDate)
                                    ->first();

                    if ($exists) {
                        DB::table('plans')
                                ->where('Sequence_No_Plan', $sequenceNo)
                                ->where('Production_Date_Plan', $productionDate)
                                ->update($data);
                        $updated++;
                    } else {
                        DB::table('plans')->insert($data);
                        $inserted++;
                    }
                }
            }
        }

        return redirect()->back()->with('success', "Import done: $inserted new data inserted, $updated data updated.");
    }

    public function recordProcessBySequence(Request $request)
    {
        $request->validate([
            'sequence_no' => 'required|string',
            'process_name' => 'required|string',
            // timestamp dihapus dari request
        ]);

        $sequenceNo = $request->input('sequence_no');
        $processName = $request->input('process_name');
        $timestamp = Carbon::now()->format('Y-m-d H:i:s'); // ✅ pakai waktu sekarang

        // --- PERUBAHAN: Format sequence_no ---
        // Format $sequenceNo yang diterima dari request ke 5 digit dengan leading zero
        // Misal: "6731" -> "06731", "1" -> "00001", "12345" -> "12345"
        $formattedSequenceNo = str_pad($sequenceNo, 5, '0', STR_PAD_LEFT);

        // 1. Cari plan berdasarkan Sequence_No_Plan (dengan format yang disesuaikan)
        $plan = DB::connection('mysql')->table('plans')->where('Sequence_No_Plan', $formattedSequenceNo)->first();
        if (!$plan) {
            return response()->json([
                'success' => false,
                'message' => "Plan dengan Sequence_No_Plan '{$formattedSequenceNo}' tidak ditemukan."
            ], 404);
        }

        $modelName = $plan->Model_Name_Plan;

        // 2. Cari rule berdasarkan Type_Rule = Model_Name_Plan
        $rule = DB::connection('mysql')->table('rules')->where('Type_Rule', $modelName)->first();
        if (!$rule) {
            return response()->json([
                'success' => false,
                'message' => "Rule untuk model '{$modelName}' tidak ditemukan."
            ], 400);
        }

        // 3. Decode Rule_Rule
        try {
            $ruleSequence = json_decode($rule->Rule_Rule, true, 512, JSON_THROW_ON_ERROR);
            if (!is_array($ruleSequence)) {
                throw new \Exception('Invalid rule format');
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => "Format rule untuk model '{$modelName}' rusak."
            ], 500);
        }

        // 4. Cek apakah process_name ada dalam rule
        $position = null;
        foreach ($ruleSequence as $key => $process) {
            if ($process === $processName) {
                $position = (int)$key;
                break;
            }
        }

        if ($position === null) {
            return response()->json([
                'success' => false,
                'message' => "Proses '{$processName}' tidak termasuk dalam rule untuk model '{$modelName}'."
            ], 400);
        }

        // 5. Decode Record_Plan
        $record = [];
        if ($plan->Record_Plan) {
            try {
                $record = json_decode($plan->Record_Plan, true, 512, JSON_THROW_ON_ERROR);
                if (!is_array($record)) $record = [];
            } catch (\Exception $e) {
                $record = [];
            }
        }

        // 6. Cek apakah proses sebelumnya sudah dilakukan
        $previousProcessesDone = true;
        $missingPrevious = [];

        for ($i = 1; $i < $position; $i++) {
            $prevProcess = $ruleSequence[$i] ?? null;
            if ($prevProcess && !isset($record[$prevProcess])) {
                $previousProcessesDone = false;
                $missingPrevious[] = $prevProcess;
            }
        }

        if (!$previousProcessesDone) {
            return response()->json([
                'success' => false,
                'message' => "Proses sebelumnya belum selesai: " . implode(', ', $missingPrevious)
            ], 400);
        }

        // 7. Update record
        $record[$processName] = $timestamp;

        // 8. Simpan kembali
        DB::connection('mysql')->table('plans')
            ->where('Id_Plan', $plan->Id_Plan)
            ->update(['Record_Plan' => json_encode($record, JSON_UNESCAPED_UNICODE)]);

        return response()->json([
            'success' => true,
            'message' => "Proses '{$processName}' berhasil dicatat untuk plan '{$formattedSequenceNo}'.",
            'data' => [
                'sequence_no' => $formattedSequenceNo, // Kirim kembali yang sudah diformat
                'process' => $processName,
                'timestamp' => $timestamp
            ]
        ]);
    }
}
