<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Plan;
use Yajra\DataTables\Facades\DataTables;

class PlanController extends Controller
{
    public function index(){
        $page = "plan";

        $Id_User = session('Id_User');
        $user = User::find($Id_User);

        $plans = Plan::orderBy('Sequence_No_Plan')->get();
        return view('admins.plans.index', compact('page', 'user', 'plans'));
    }

    public function getPlans()
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

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('action', function ($row) {
                $deleteModalId = "deleteModal-".$row->Id_Plan;

                return '
                    <a href="'.route('plan.edit', $row->Id_Plan).'" 
                    class="btn btn-sm btn-outline-primary">
                        <span class="tf-icons bx bx-edit"></span>
                    </a>

                    <!-- Delete Button -->
                    <button type="button" 
                            class="btn btn-sm btn-outline-danger" 
                            data-bs-toggle="modal" 
                            data-bs-target="#'.$deleteModalId.'">
                        <span class="tf-icons bx bx-trash"></span>
                    </button>

                    <!-- Delete Modal -->
                    <div class="modal fade" id="'.$deleteModalId.'" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <form action="'.route('plan.destroy', $row->Id_Plan).'" method="POST">
                                    '.csrf_field().method_field('DELETE').'
                                    <div class="modal-header bg-danger">
                                        <h5 class="modal-title text-white">Delete Plan</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        Apakah kamu yakin ingin menghapus plan 
                                        <strong>'.$row->Sequence_No_Plan.'</strong>?
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                                        <button type="submit" class="btn btn-danger">Delete</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
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

    public function destroy(Plan $Id_Plan)
    {
        $Id_Plan->delete();
        
        return redirect()->route('plan')->with('success','Data berhasil di hapus' );
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
                    $exists = DB::table('plans')->where('Sequence_No_Plan', $sequenceNo)->first();

                    if ($exists) {
                        DB::table('plans')->where('Sequence_No_Plan', $sequenceNo)->update($data);
                        $updated++;
                    } else {
                        DB::table('plans')->insert($data);
                        $inserted++;
                    }
                }
            }
        }

        return redirect()->back()->with('success', "Import selesai: $inserted data baru ditambahkan, $updated data diperbarui.");
    }
}
