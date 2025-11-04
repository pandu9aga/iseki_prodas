<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Type_User;
use App\Models\Plan;
use Yajra\DataTables\Facades\DataTables;

class MainController extends Controller
{
    public function index(){
        if (session()->has('Id_User')) {
            if (session('Id_Type_User') == 2){
                return redirect()->route('dashboard');
            }
            else if (session('Id_Type_User') == 1){
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

        $user = User::where('Username_User', $request->Username_User)->first();

        if (!$user) {
            return back()->withErrors(['loginError' => 'Invalid username or password']);
        }

        if ($request->Password_User == $user->Password_User) {
            session(['Id_User' => $user->Id_User]);
            session(['Id_Type_User' => $user->Id_Type_User]);
            session(['Username_User' => $user->Username_User]);
            if (session('Id_Type_User') == 2){
                return redirect()->route('dashboard');
            }
            else if (session('Id_Type_User') == 1){
                return redirect()->route('home');
            }
        }

        return back()->withErrors(['loginError' => 'Invalid username or password']);
    }

    public function logout()
    {
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
            // Update kolom Lineoff_Plan di tabel plans
            $updatedRows = DB::table('plans')
                ->where('Sequence_No_Plan', $sequenceNoFormatted)
                ->update([
                    'Lineoff_Plan' => $timestampNow
                ]);

            if ($updatedRows > 0) {
                return redirect()->back()->with('success', "Data Lineoff untuk Sequence No {$sequenceNoFormatted} berhasil diperbarui.");
            } else {
                return redirect()->back()->withErrors(['sequence_no' => "Plan dengan Sequence_No_Plan '{$sequenceNoFormatted}' tidak ditemukan."]);
            }

        } catch (\Exception $e) {
            // Log error jika perlu
            // \Log::error('Gagal update Lineoff_Plan: ' . $e->getMessage());
            return redirect()->back()->withErrors(['general' => 'Gagal memperbarui data Lineoff: ' . $e->getMessage()]);
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