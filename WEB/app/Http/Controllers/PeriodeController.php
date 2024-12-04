<?php

namespace App\Http\Controllers;

use App\Models\PeriodeModel;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use PhpOffice\PhpSpreadsheet\IOFactory;
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

    public function export_excel()
    {
        // ambil data periode yang akan di export
        $periode = PeriodeModel::select('periode', 'tanggal_mulai', 'tanggal_selesai', 'status')->get();

        // load library excel
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->setCellValue('A1', 'No');
        $sheet->setCellValue('B1', 'periode');
        $sheet->setCellValue('C1', 'tanggal mulai');
        $sheet->setCellValue('D1', 'tanggal akhir');
        $sheet->setCellValue('E1', 'status');

        $sheet->getStyle('A1:E1')->getFont()->setBold(true);

        $no = 1; // no data dimulai dari 1
        $baris = 2; // baris data dimulai dari baris ke 2
        foreach ($periode as $key => $value) {
            $sheet->setCellValue('A' . $baris, $no);
            $sheet->setCellValue('B' . $baris, $value->periode);
            $sheet->setCellValue('C' . $baris, $value->tanggal_mulai);
            $sheet->setCellValue('D' . $baris, $value->tanggal_selesai);
            $sheet->setCellValue('E' . $baris, $value->status);
            $baris++;
            $no++;
        }

        foreach (range('A', 'E') as $columnID) {
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
        }

        $sheet->setTitle('Data Periode');
        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
        $filename = 'Data periode ' . date('Y-m-d H:i:s') . '.xlsx';

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
        $periode = periodeModel::select('periode', 'tanggal_mulai', 'tanggal_selesai', 'status')->get();

        $pdf = Pdf::loadView('periode.export_pdf', ['periode' => $periode]);
        $pdf->setPaper('a4', 'potrait');
        $pdf->setOption('isRemoteEnabled', true);
        $pdf->render();

        return $pdf->stream('Data Periode' . date('Y-m-d H:i:s') . '.pdf');
    }

}
