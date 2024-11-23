<?php

namespace App\Http\Controllers;

use App\Models\PeranModel;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Yajra\DataTables\Facades\DataTables;

class PeranController extends Controller
{
    public function index()
    {
        $activeMenu = 'peran';
        $breadcrumb = (object) [
            'title' => 'Daftar Peran',
            'list' => ['Home', 'Peran']
        ];

        $peran = PeranModel::all();
        return view('peran.index', [
            'activeMenu' => $activeMenu,
            'breadcrumb' => $breadcrumb,
            'peran' => $peran,
        ]);
    }

    public function list(Request $request)
    {
        $perans = PeranModel::select('peran_id', 'peran_kode', 'peran_nama');


        return DataTables::of($perans)
            ->addIndexColumn()
            ->addColumn('aksi', function ($peran) {
                $btn  = '<button onclick="modalAction(\''.url('/peran/' . $peran->peran_id .
                '/show_ajax').'\')" class="btn btn-info btn-sm">Detail</button> ';
                $btn .= '<button onclick="modalAction(\''.url('/peran/' . $peran->peran_id .
                '/edit_ajax').'\')" class="btn btn-warning btn-sm">Edit</button> ';
                $btn .= '<button onclick="modalAction(\''.url('/peran/' . $peran->peran_id .
                '/delete_ajax').'\')"  class="btn btn-danger btn-sm">Hapus</button> ';
                return $btn;
            })
            ->rawColumns(['aksi'])
            ->make(true);
    }

    public function create_ajax()
    {
        $peran = PeranModel::select('peran_id', 'peran_kode', 'peran_nama')->get();

        return view('peran.create_ajax')->with('peran', $peran);
    }

    public function store_ajax(Request $request)
    {

        if($request->ajax() || $request->wantsJson()) {
            $rules = [
                'peran_kode' => ['required', 'string', 'min:3', 'max:10', 'unique:m_peran,peran_kode'],
                'peran_nama' => ['required', 'string', 'max:100'],
            ];

            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validasi Gagal',
                    'msgField' => $validator->errors(),
                ]);
            }

            PeranModel::create($request->all());
            return response()->json([
                'status' => true,
                'message' => 'Data peran berhasil disimpan'
            ]);
        }
        return redirect('/');
    }

    public function show_ajax(string $id)
    {
        $peran = PeranModel::find($id);
        return view('peran.show_ajax')->with(['peran' => $peran]);
    }

    public function edit_ajax(string $id)
    {
        $peran = PeranModel::find($id);
        return view('peran.edit_ajax')->with(['peran' => $peran]);
    }

    public function update_ajax(Request $request, $id)
    {
        // cek apakah request dari ajax
        if ($request->ajax() || $request->wantsJson()) {
            $rules = [
                'peran_kode' => ['required', 'string', 'min:3', 'max:10', 'unique:m_peran,peran_kode,'.$id.',peran_id'],
                'peran_nama' => ['required', 'string', 'max:100'],
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

            $check = PeranModel::find($id);
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
        $peran = PeranModel::find($id);
        return view('peran.confirm_ajax')->with(['peran' => $peran]);
    }

    public function delete_ajax(Request $request, string $id)
    {
        if ($request->ajax() || $request->wantsJson()) {
            $peran = PeranModel::find($id);

            if ($peran) {
                $peran->delete();
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
        // ambil data peran yang akan di export
        $peran = PeranModel::select('peran_kode', 'peran_nama')->get();

        // load library excel
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->setCellValue('A1', 'No');
        $sheet->setCellValue('B1', 'Kode Peran');
        $sheet->setCellValue('C1', 'Nama Peran');

        $sheet->getStyle('A1:C1')->getFont()->setBold(true);

        $no = 1; // no data dimulai dari 1
        $baris = 2; // baris data dimulai dari baris ke 2
        foreach ($peran as $key => $value) {
            $sheet->setCellValue('A' . $baris, $no);
            $sheet->setCellValue('B' . $baris, $value->peran_kode);
            $sheet->setCellValue('C' . $baris, $value->peran_nama);
            $baris++;
            $no++;
        }

        foreach (range('A', 'C') as $columnID) {
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
        }

        $sheet->setTitle('Data peran');
        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
        $filename = 'Data peran ' . date('Y-m-d H:i:s') . '.xlsx';

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
        $peran = PeranModel::select('peran_kode', 'peran_nama')->get();

        $pdf = Pdf::loadView('peran.export_pdf', ['peran' => $peran]);
        $pdf->setPaper('a4', 'potrait');
        $pdf->setOption('isRemoteEnabled', true);
        $pdf->render();

        return $pdf->stream('Data Peran ' . date('Y-m-d H:i:s') . '.pdf');
    }
}
