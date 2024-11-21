<?php

namespace App\Http\Controllers;

use App\Models\PeriodeModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;

class PeriodeController extends Controller
{
    public function index()
    {
        $activeMenu = 'periode';
        $breadcrumb = (object) [
            'title' => 'Daftar Periode',
            'list' => ['Home', 'Periode']
        ];

        $periode = PeriodeModel::all();

        return view('periode.index', [
            'activeMenu' => $activeMenu,
            'breadcrumb' => $breadcrumb,
            'periode' => $periode
        ]);
    }

    public function list(Request $request)
    {
        $periode = PeriodeModel::select('periode_id', 'periode', 'tanggal_mulai', 'tanggal_selesai', 'status');

        return DataTables::of($periode)
            ->addIndexColumn()
            ->addColumn('aksi', function ($periode) {
                $btn  = '<button onclick="modalAction(\''.url('/periode/' . $periode->periode_id .
                '/show_ajax').'\')" class="btn btn-info btn-sm">Detail</button> ';
                $btn .= '<button onclick="modalAction(\''.url('/periode/' . $periode->periode_id .
                '/edit_ajax').'\')" class="btn btn-warning btn-sm">Edit</button> ';
                $btn .= '<button onclick="modalAction(\''.url('/periode/' . $periode->periode_id .
                '/delete_ajax').'\')"  class="btn btn-danger btn-sm">Hapus</button> ';
                return $btn;
            })
            ->rawColumns(['aksi'])
            ->make(true);
    }

    public function create_ajax()
    {
        $periode = PeriodeModel::select('periode_id', 'periode')->get();
        return view('periode.create_ajax')->with('periode', $periode);
    }

    public function store_ajax(Request $request)
    {

        if($request->ajax() || $request->wantsJson()) {
            $rules = [
                'periode' => ['required','string','min:3'],
                'tanggal_mulai' => ['required','date'],
                'tanggal_selesai' => ['required','date'],
                'status' => ['required','in:Aktif,Tidak Aktif'],
            ];

            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validasi Gagal',
                    'msgField' => $validator->errors(),
                ]);
            }

            PeriodeModel::create($request->all());
            return response()->json([
                'status' => true,
                'message' => 'Data periode berhasil disimpan'
            ]);
        }
        return redirect('/');
    }

    public function show_ajax(string $id)
    {
        $periode = PeriodeModel::find($id);
        return view('periode.show_ajax', ['periode' => $periode]);
    }

    public function edit_ajax(string $id)
    {
        $periode = PeriodeModel::find($id);

        return view('periode.edit_ajax', ['periode' => $periode]);
    }

    public function update_ajax(Request $request, $id)
    {
        // cek apakah request dari ajax
        if ($request->ajax() || $request->wantsJson()) {
            $rules = [
                'periode' => ['required','string','min:3'],
                'tanggal_mulai' => ['required','date'],
                'tanggal_selesai' => ['required','date'],
                'status' => ['required','in:Aktif,Tidak Aktif'],
            ];

            // use Illuminate\Support\Facades\Validator;
            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                return response()->json([
                    'status'   => false,    // respon json, true: berhasil, false: gagal
                    'message'  => 'Validasi gagal.',
                    'msgField' => $validator->errors()  // menunjukkan field mana yang error
                ]);
            }

            $check = PeriodeModel::find($id);
            if ($check) {
                $check->update($request->all());
                return response()->json([
                    'status'  => true,
                    'message' => 'Data berhasil diupdate'
                ]);
            } else {
                return response()->json([
                    'status'  => false,
                    'message' => 'Data tidak ditemukan'
                ]);
            }
        }
        return redirect('/');
    }

    public function confirm_ajax(string $id)
    {
        $periode = PeriodeModel::find($id);

        return view('periode.confirm_ajax', ['periode' => $periode]);
    }

    public function delete_ajax(Request $request, string $id)
    {
        if ($request->ajax() || $request->wantsJson()) {
            $periode = PeriodeModel::find($id);

            if ($periode) {
                $periode->delete();
                return response()->json([
                    'status' => true,
                    'message' => 'Data berhasil dihapus'
                ]);
            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'Data tidak ditemukan'
                ]);
            }
            return redirect('/');
        }
    }

}
