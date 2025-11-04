<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use PhpOffice\PhpSpreadsheet\IOFactory;
use App\Models\User;
use App\Models\Rule;
use Yajra\DataTables\Facades\DataTables;

class RuleController extends Controller
{
    public function index(){
        $page = "rule";

        $Id_User = session('Id_User');
        $user = User::find($Id_User);

        $rules = Rule::all();
        return view('admins.rules.index', compact('page', 'user', 'rules'));
    }

    public function getRules()
    {
        $query = Rule::select([
            'Id_Rule',
            'Type_Rule',
            'Rule_Rule', // ini sekarang array karena casting
        ]);

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('action', function ($row) {
                return '
                    <a href="'.route('rule.edit', $row->Id_Rule).'" class="btn btn-sm btn-outline-primary">
                        <span class="tf-icons bx bx-edit"></span>
                    </a>
                    <button class="btn btn-sm btn-outline-danger delete-btn" 
                            data-id="'.$row->Id_Rule.'" 
                            data-name="'.$row->Type_Rule.'">
                        <span class="tf-icons bx bx-trash"></span>
                    </button>
                ';
            })
            ->editColumn('Rule_Rule', function ($row) {
                // Karena Rule_Rule adalah array, encode ke JSON string
                return json_encode($row->Rule_Rule ?? []);
            })
            ->rawColumns(['action', 'Rule_Rule']) // ✅ izinkan HTML di Rule_Rule (nanti di-render sebagai list)
            ->make(true);
    }

    public function add()
    {
        $page = "rule";

        $Id_User = session('Id_User');
        $user = User::find($Id_User);

        return view('admins.rules.add', compact('page', 'user'));
    }

    public function create(Request $request)
    {
        // Validasi
        $request->validate([
            'Type_Rule' => 'required|string|max:255',
            'Rule_Rule' => 'nullable|string', // boleh null/kosong
        ], [
            'Type_Rule.required' => 'Type wajib diisi',
        ]);

        // Ambil input
        $typeRule = $request->input('Type_Rule');
        $ruleRule = $request->input('Rule_Rule'); // ini string JSON

        // Opsional: Validasi isi JSON (pastikan hanya nilai yang diizinkan)
        $allowedValues = [
            'chadet',
            'parcom_ring_synchronizer',
            'astra_engine',
            'astra_main_line_start',
            'astra_main_line_end',
            'astra_mower_collector',
            'oiler'
        ];

        if ($ruleRule) {
            $decoded = json_decode($ruleRule, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw ValidationException::withMessages([
                    'Rule_Rule' => 'Format Rule_Rule tidak valid (bukan JSON).'
                ]);
            }

            // Cek apakah semua nilai diizinkan
            foreach ($decoded as $key => $value) {
                if (!is_string($value) || !in_array($value, $allowedValues)) {
                    throw ValidationException::withMessages([
                        'Rule_Rule' => "Nilai rule '{$value}' tidak diizinkan."
                    ]);
                }
            }

            // Cek duplikat (opsional)
            if (count($decoded) !== count(array_unique($decoded))) {
                throw ValidationException::withMessages([
                    'Rule_Rule' => 'Tidak boleh ada rule yang duplikat.'
                ]);
            }
        } else {
            // Jika tidak ada rule, simpan sebagai JSON kosong
            $ruleRule = '{}';
        }

        // Simpan ke database
        DB::table('rules')->insert([
            'Type_Rule' => $typeRule,
            'Rule_Rule' => $ruleRule, // string JSON
        ]);

        return redirect()->route('rule')->with('success', 'Rule berhasil ditambahkan.');
    }

    public function edit(Rule $Id_Rule)
    {
        $page = "rule";

        $Id_User_Session = session('Id_User');
        $user = User::find($Id_User_Session);

        return view('admins.rules.edit', compact('page', 'user', 'Id_Rule'));
    }

   public function update(Request $request, string $Id_Rule)
    {
        // Validasi input
        $request->validate([
            'Type_Rule' => 'required|string|max:255',
            'Rule_Rule' => 'nullable|string',
        ], [
            'Type_Rule.required' => 'Type wajib diisi',
        ]);

        // Ambil input
        $typeRule = $request->input('Type_Rule');
        $ruleRule = $request->input('Rule_Rule'); // string JSON dari hidden input

        // Daftar nilai rule yang diizinkan
        $allowedValues = [
            'chadet',
            'parcom_ring_synchronizer',
            'astra_engine',
            'astra_main_line_start',
            'astra_main_line_end',
            'astra_mower_collector',
            'oiler'
        ];

        // Proses Rule_Rule
        if ($ruleRule) {
            $decoded = json_decode($ruleRule, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw ValidationException::withMessages([
                    'Rule_Rule' => 'Format Rule_Rule tidak valid (bukan JSON).'
                ]);
            }

            if (!is_array($decoded)) {
                throw ValidationException::withMessages([
                    'Rule_Rule' => 'Rule_Rule harus berupa objek JSON.'
                ]);
            }

            // Validasi setiap nilai
            foreach ($decoded as $key => $value) {
                if (!is_string($value) || !in_array($value, $allowedValues)) {
                    throw ValidationException::withMessages([
                        'Rule_Rule' => "Nilai rule '{$value}' tidak diizinkan."
                    ]);
                }
            }

            // Cek duplikat
            if (count($decoded) !== count(array_unique($decoded))) {
                throw ValidationException::withMessages([
                    'Rule_Rule' => 'Tidak boleh ada rule yang duplikat.'
                ]);
            }

            // Simpan sebagai string JSON yang sudah divalidasi
            $finalRuleRule = json_encode($decoded, JSON_UNESCAPED_UNICODE);
        } else {
            // Jika tidak ada rule, simpan JSON kosong
            $finalRuleRule = '{}';
        }

        // Update ke database
        $updated = DB::table('rules')
            ->where('Id_Rule', $Id_Rule)
            ->update([
                'Type_Rule' => $typeRule,
                'Rule_Rule' => $finalRuleRule,
            ]);

        if (!$updated) {
            // Opsional: handle jika tidak ada perubahan atau data tidak ditemukan
            return redirect()->back()->with('error', 'Data tidak ditemukan atau tidak ada perubahan.');
        }

        return redirect()->route('rule')->with('success', 'Rule berhasil diperbarui.');
    }

    public function destroy(Rule $Id_Rule)
    {
        $Id_Rule->delete();
        
        return redirect()->route('rule')->with('success','Data berhasil di hapus' );
    }

    public function import(Request $request)
    {
        $request->validate([
            'excel' => 'required|mimes:xlsx,xls|max:2048',
        ], [
            'excel.required' => 'File Excel wajib diunggah.',
            'excel.mimes' => 'Format file harus .xlsx atau .xls.',
            'excel.max' => 'Ukuran file maksimal 2 MB.',
        ]);

        // Daftar rule yang diizinkan (sesuaikan dengan kebutuhan)
        $allowedValues = [
            'chadet',
            'parcom_ring_synchronizer',
            'astra_engine',
            'astra_main_line_start',
            'astra_main_line_end',
            'astra_mower_collector',
            'oiler'
        ];

        try {
            $file = $request->file('excel');
            $spreadsheet = IOFactory::load($file);
            $worksheet = $spreadsheet->getActiveSheet();
            $rows = $worksheet->toArray(null, true, true, true);

            $inserted = 0;
            $updated = 0;
            $errors = [];

            foreach ($rows as $index => $row) {
                $type = trim($row['A'] ?? '');
                if ($type === '') {
                    continue; // lewati baris kosong
                }

                // Ambil rule dari kolom B ke kanan (berhenti saat kolom kosong)
                $ruleValues = [];
                for ($col = 'B'; $col <= 'Z'; $col++) {
                    $value = trim($row[$col] ?? '');
                    if ($value === '') {
                        break; // asumsi tidak ada celah
                    }
                    if (!in_array($value, $allowedRules)) {
                        $errors[] = "Baris " . ($index + 1) . ": Rule '{$value}' tidak diizinkan.";
                        continue 2;
                    }
                    $ruleValues[] = $value;
                }

                // Cek duplikat dalam satu baris
                if (count($ruleValues) !== count(array_unique($ruleValues))) {
                    $errors[] = "Baris " . ($index + 1) . ": Terdapat rule duplikat.";
                    continue;
                }

                // Buat JSON urutan
                $ruleJson = json_encode(
                    array_combine(range(1, count($ruleValues)), $ruleValues),
                    JSON_UNESCAPED_UNICODE
                );

                // Cek apakah Type_Rule sudah ada
                $exists = DB::table('rules')->where('Type_Rule', $type)->exists();

                if ($exists) {
                    DB::table('rules')->where('Type_Rule', $type)->update(['Rule_Rule' => $ruleJson]);
                    $updated++;
                } else {
                    DB::table('rules')->insert([
                        'Type_Rule' => $type,
                        'Rule_Rule' => $ruleJson,
                    ]);
                    $inserted++;
                }
            }

            // Siapkan pesan sukses
            $message = "Import done: {$inserted} new data inserted, {$updated} data updated.";
            if (!empty($errors)) {
                $message .= " Errors: " . implode(' | ', $errors);
                return redirect()->back()->with('error', $message);
            }

            return redirect()->back()->with('success', $message);

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Import gagal: ' . $e->getMessage());
        }
    }
}