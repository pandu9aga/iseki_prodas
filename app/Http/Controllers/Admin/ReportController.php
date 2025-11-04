<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;
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
            // Hanya data yang ada huruf T/t
            $q->where('Sequence_No_Plan', 'REGEXP', '^[Tt]');
        })
        ->orderByRaw("
            LENGTH(Sequence_No_Plan),
            CAST(REGEXP_REPLACE(Sequence_No_Plan, '[^0-9]', '') AS UNSIGNED)
        ");

        if ($type === 'unit') {
            $query->where('Sequence_No_Plan', 'NOT REGEXP', '[Tt]');
        } elseif ($type === 'nonunit') {
            $query->where('Sequence_No_Plan', 'REGEXP', '^[Tt]');
        }

        // Filter berdasarkan range
        if ($min && $max) {
            if ($type === 'unit') {
                $query->whereBetween(DB::raw('CAST(Sequence_No_Plan AS UNSIGNED)'), [$min, $max]);
            } else {
                $query->whereRaw("CAST(SUBSTRING(Sequence_No_Plan, 2) AS UNSIGNED) BETWEEN ? AND ?", [$min, $max])
                    ->orderByRaw("CAST(SUBSTRING(Sequence_No_Plan, 2) AS UNSIGNED) ASC");
            }
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

}
