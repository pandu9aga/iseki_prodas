<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class NgProcessController extends Controller
{
    public function index()
    {
        $page = "ng-processes";
        $sub = "ng-processes";

        $Id_User = session('Id_User');
        $user = \App\Models\User::find($Id_User);

        return view('admins.ng_processes.index', compact('page', 'sub', 'user'));
    }

    public function getData(Request $request)
    {
        $query = \App\Models\NgProcess::query()
            ->orderBy('id', 'desc');

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('created_at', [
                $request->start_date . ' 00:00:00',
                $request->end_date . ' 23:59:59'
            ]);
        }

        if ($request->filled('app_name')) {
            $query->where('app_name', $request->app_name);
        }

        return \Yajra\DataTables\Facades\DataTables::of($query)
            ->addIndexColumn()
            ->editColumn('created_at', function ($row) {
                return $row->created_at ? \Carbon\Carbon::parse($row->created_at)->format('Y-m-d H:i:s') : '-';
            })
            ->make(true);
    }
}
