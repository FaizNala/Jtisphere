<?php

namespace App\Http\Controllers;

use App\Models\KategoriModel;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Yajra\DataTables\Facades\DataTables;

class kategoriController extends Controller
{
    public function index()
    {
        $activeMenu = 'kategori';
        $breadcrumb = (object) [
            'title' => 'Daftar Kategori',
            'list' => ['Home', 'Kategori']
        ];

        $kategori = KategoriModel::all();
        return view('kategori.index', [
            'activeMenu' => $activeMenu,
            'breadcrumb' => $breadcrumb,
            'kategori' => $kategori,
        ]);
    }

    public function list(Request $request)
    {
        $kategoris = KategoriModel::select('kategori_id', 'kategori_nama');


        return DataTables::of($kategoris)
            ->addIndexColumn()
            ->addColumn('aksi', function ($kategori) {
                $btn  = '<button onclick="modalAction(\''.url('/kategori/' . $kategori->kategori_id .
                '/show_ajax').'\')" class="btn btn-info btn-sm">Detail</button> ';
                $btn .= '<button onclick="modalAction(\''.url('/kategori/' . $kategori->kategori_id .
                '/edit_ajax').'\')" class="btn btn-warning btn-sm">Edit</button> ';
                $btn .= '<button onclick="modalAction(\''.url('/kategori/' . $kategori->kategori_id .
                '/delete_ajax').'\')"  class="btn btn-danger btn-sm">Hapus</button> ';
                return $btn;
            })
            ->rawColumns(['aksi'])
            ->make(true);
    }

    public function create_ajax()
    {
        $kategori = KategoriModel::select('kategori_id', 'kategori_nama')->get();

        return view('kategori.create_ajax')->with('kategori', $kategori);
    }

    public function store_ajax(Request $request)
    {

        if($request->ajax() || $request->wantsJson()) {
            $rules = [
                'kategori_nama' => ['required', 'string', 'max:100'],
            ];

            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validasi Gagal',
                    'msgField' => $validator->errors(),
                ]);
            }

            KategoriModel::create($request->all());
            return response()->json([
                'status' => true,
                'message' => 'Data kategori berhasil disimpan'
            ]);
        }
        return redirect('/');
    }

    public function show_ajax(string $id)
    {
        $kategori = KategoriModel::find($id);
        return view('kategori.show_ajax')->with(['kategori' => $kategori]);
    }

    public function edit_ajax(string $id)
    {
        $kategori = KategoriModel::find($id);
        return view('kategori.edit_ajax')->with(['kategori' => $kategori]);
    }

    public function update_ajax(Request $request, $id)
    {
        // cek apakah request dari ajax
        if ($request->ajax() || $request->wantsJson()) {
            $rules = [
                'kategori_nama' => ['required', 'string', 'max:100'],
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

            $check = KategoriModel::find($id);
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
        $kategori = KategoriModel::find($id);
        return view('kategori.confirm_ajax')->with(['kategori' => $kategori]);
    }

    public function delete_ajax(Request $request, string $id)
    {
        if ($request->ajax() || $request->wantsJson()) {
            $kategori = KategoriModel::find($id);

            if ($kategori) {
                $kategori->delete();
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

    public function export_excel()
    {
        // ambil data kategori yang akan di export
        $kategori = KategoriModel::select('kategori_nama')->get();

        // load library excel
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->setCellValue('A1', 'No');
        $sheet->setCellValue('B1', 'Nama Kategori');

        $sheet->getStyle('A1:B1')->getFont()->setBold(true);

        $no = 1; // no data dimulai dari 1
        $baris = 2; // baris data dimulai dari baris ke 2
        foreach ($kategori as $key => $value) {
            $sheet->setCellValue('A' . $baris, $no);
            $sheet->setCellValue('B' . $baris, $value->kategori_nama);
            $baris++;
            $no++;
        }

        foreach (range('A', 'B') as $columnID) {
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
        }

        $sheet->setTitle('Data Kategori');
        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
        $filename = 'Data Kategori ' . date('Y-m-d H:i:s') . '.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        header('Cache-Control: max-age=1');
        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . 'GMT');
        header('Cache-Control: cache, must-revalidate');
        header('Pragma: public');

        $writer->save('php://output');
        exit;
    }

    public function export_pdf()
    {
        $kategori = KategoriModel::select('kategori_nama')->get();

        $pdf = Pdf::loadView('kategori.export_pdf', ['kategori' => $kategori]);
        $pdf->setPaper('a4', 'potrait');
        $pdf->setOption('isRemoteEnabled', true);
        $pdf->render();

        return $pdf->stream('Data Kategori ' . date('Y-m-d H:i:s') . '.pdf');
    }
}
