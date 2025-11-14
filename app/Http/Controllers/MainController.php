<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Type_User;
use App\Models\Plan;
use Yajra\DataTables\Facades\DataTables;

class MainController extends Controller
{
    // public function index(){
    //     if (session()->has('Id_User')) {
    //         if (session('Id_Type_User') == 2){
    //             return redirect()->route('dashboard');
    //         }
    //         else if (session('Id_Type_User') == 1){
    //             return redirect()->route('home');
    //         }
    //     }
    //     return view('auth.login');
    // }

    // public function login(Request $request)
    // {
    //     $request->validate([
    //         'Username_User' => 'required',
    //         'Password_User' => 'required'
    //     ]);

    //     $user = User::where('Username_User', $request->Username_User)->first();

    //     if (!$user) {
    //         return back()->withErrors(['loginError' => 'Invalid username or password']);
    //     }

    //     if ($request->Password_User == $user->Password_User) {
    //         session(['Id_User' => $user->Id_User]);
    //         session(['Id_Type_User' => $user->Id_Type_User]);
    //         session(['Username_User' => $user->Username_User]);
    //         if (session('Id_Type_User') == 2){
    //             return redirect()->route('dashboard');
    //         }
    //         else if (session('Id_Type_User') == 1){
    //             return redirect()->route('home');
    //         }
    //     }

    //     return back()->withErrors(['loginError' => 'Invalid username or password']);
    // }

    // public function logout()
    // {
    //     session()->forget('Id_User');
    //     session()->forget('Id_Type_User');
    //     session()->forget('Username_User');
    //     return redirect()->route('/');
    // }

    public function index()
    {
        if (Auth::check()) {
            $user = Auth::user();

            if ($user->Id_Type_User == 2) {
                return redirect()->route('dashboard');
            } elseif ($user->Id_Type_User == 1) {
                return redirect()->route('home');
            }
        }

        return view('auth.login');
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

    public function logout()
    {
        Auth::logout();

        session()->forget('Id_User');
        session()->forget('Id_Type_User');
        session()->forget('Username_User');
        
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

    public function scan(){
        return view('lineoff.scan');
    }

    public function scanStore(Request $request)
    {
        $request->validate([
            'sequence_no' => 'required|string|max:255',
        ]);

        $sequenceNo = $request->input('sequence_no');

        // Format sequence_no ke 5 digit dengan leading zero (jika belum oleh JS)
        $sequenceNoFormatted = str_pad($sequenceNo, 5, '0', STR_PAD_LEFT);

        $timestampNow = Carbon::now();

        try {
            // 1. Update kolom Lineoff_Plan di tabel plans
            $updatedRows = DB::table('plans')
                ->where('Sequence_No_Plan', $sequenceNoFormatted)
                ->update([
                    'Lineoff_Plan' => $timestampNow
                ]);

            if ($updatedRows === 0) {
                return redirect()->back()->withErrors(['sequence_no' => "Plan dengan Sequence_No_Plan '{$sequenceNoFormatted}' tidak ditemukan."]);
            }

            // 2. Ambil plan yang baru diupdate untuk pengecekan status
            $plan = DB::table('plans')
                ->where('Sequence_No_Plan', $sequenceNoFormatted)
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
                ->update(['Status_Plan' => $newStatus]);

            $statusMessage = $allProcessesCompleted ? " dan Status Plan: Done." : " dan Status Plan: Pending.";

            // Redirect ke route 'lineoff' setelah sukses
            return redirect()->route('lineoff')->with('success', "Data Lineoff untuk Sequence No {$sequenceNoFormatted} berhasil diperbarui." . $statusMessage);

        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['general' => 'Gagal memperbarui data Lineoff dan Status: ' . $e->getMessage()]);
        }
    }

    public function lineoff(){
        return view('lineoff.list');
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
            'Lineoff_Plan'
        ])
        ->whereNotNull('Lineoff_Plan')
        ->orderBy('Lineoff_Plan', 'desc');

        return DataTables::of($query)
            ->addIndexColumn()
            ->make(true);
    }
}