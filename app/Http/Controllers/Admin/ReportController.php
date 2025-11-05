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

}
