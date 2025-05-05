<?php

namespace App\Http\Controllers;

use App\Models\AgendaDosenModel;
use App\Models\AgendaModel;
use App\Models\BuktiAgendaModel;
use App\Models\DokumenModel;
use App\Models\KegiatanAgendaModel;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use Yajra\DataTables\Facades\DataTables;

class AgendaDosenController extends Controller
{
    public function index()
    {
        $activeMenu = 'agenda_dosen';
        $breadcrumb = (object) [
            'title' => 'Daftar Agenda Dosen',
            'list' => ['Home', 'Agenda Dosen']
        ];

        return view('agenda_dosen.index', [
            'activeMenu' => $activeMenu,
            'breadcrumb' => $breadcrumb,
        ]);
    }

    public function list(Request $request)
    {
        $agenda = AgendaModel::with([
            'kegiatanAgenda',
            'kegiatanAgenda.kegiatan' // Load the kegiatan through kegiatanAgenda
        ])
            ->whereHas('agendaDosen', function ($query) {
                $dosenId = session('dosen_id');
                $query->where('dosen_id', $dosenId);
            })
            ->get(); // Explicitly get the collection

        return DataTables::of($agenda)
            ->addIndexColumn()
            ->addColumn('kegiatan_nama', function ($agenda) {
                // Explicitly name the column 'kegiatan_nama'
                // $kegiatanAgenda = $agenda->kegiatanAgenda->first();
                return $agenda->kegiatanAgenda->first()->kegiatan->kegiatan_nama;
            })
            ->addColumn('status', function ($agenda) {
                $kegiatanAgenda = $agenda->kegiatanAgenda->first();
                return $kegiatanAgenda->status;
            })
            ->addColumn('aksi', function ($agenda) {
                $btn  = '<button onclick="modalAction(\'' . url('/agenda_dosen/' . $agenda->agenda_id . '/show_ajax') . '\')" class="btn btn-info btn-sm">Detail</button> ';
                $btn .= '<button onclick="modalAction(\'' . url('/agenda_dosen/' . $agenda->agenda_id . '/edit_ajax') . '\')" class="btn btn-warning btn-sm">Update Progres</button> ';
                return $btn;
            })
            ->rawColumns(['aksi'])
            ->make(true);
    }


    public function show_ajax($id)
    {
        $agenda = DB::table('t_agenda as a')
            ->join('t_kegiatan_agenda as ka', 'a.agenda_id', '=', 'ka.agenda_id')
            ->join('t_kegiatan as k', 'ka.kegiatan_id', '=', 'k.kegiatan_id')
            ->select('a.*', 'ka.*', 'k.*')
            ->where('a.agenda_id', $id)
            ->first();

        $dosen = DB::table('m_dosen as d')
            ->join('t_agenda_dosen as ad', 'd.dosen_id', '=', 'ad.dosen_id')
            ->join('t_kegiatan_agenda as ka', 'ad.agenda_id', '=', 'ka.agenda_id')
            ->join('t_kegiatan as k', 'ka.kegiatan_id', '=', 'k.kegiatan_id')
            ->join('t_agenda as a', 'a.agenda_id', '=', 'ka.agenda_id')
            ->leftJoin('t_bukti_agenda as ba', 'ba.agenda_dosen_id', '=', 'ad.agenda_dosen_id')
            ->leftJoin('m_dokumen as b', 'ba.dokumen_id', '=', 'b.dokumen_id')
            ->select('d.dosen_id', 'd.nama', 'b.dokumen_nama') // Ganti dengan kolom yang relevan
            ->where('a.agenda_id', $id)
            ->get();


        $agenda->dosen = $dosen; // Set the dosen property on the $agenda object

        return view('agenda_dosen.show_ajax', [
            'agenda' => $agenda,
            'dosenList' => $dosen,
        ]);
    }

    public function edit_ajax($id)
    {
        // Ambil data agenda
        $agenda = DB::table('t_agenda as a')
            ->join('t_kegiatan_agenda as ka', 'a.agenda_id', '=', 'ka.agenda_id')
            ->join('t_kegiatan as k', 'ka.kegiatan_id', '=', 'k.kegiatan_id')
            ->select('a.*', 'ka.status', 'k.kegiatan_id', 'k.kegiatan_nama')
            ->where('a.agenda_id', $id)
            ->first();

        // Ambil bukti agenda
        $buktiAgenda = DB::table('t_bukti_agenda as ba')
            ->join('m_dokumen as d', 'ba.dokumen_id', '=', 'd.dokumen_id')
            ->join('t_agenda_dosen as ad', 'ba.agenda_dosen_id', '=', 'ad.agenda_dosen_id')
            ->join('t_agenda as a', 'ad.agenda_id', '=', 'a.agenda_id')
            ->select('d.*', 'ba.*') // Tambahkan seleksi untuk detail bukti agenda
            ->where('a.agenda_id', $id)
            ->get();

        return view('agenda_dosen.edit_ajax', [
            'agenda' => $agenda,
            'buktiAgenda' => $buktiAgenda,
        ]);
    }

    public function update_ajax(Request $request, $id)
    {
        if ($request->ajax() || $request->wantsJson()) {
            // Aturan validasi
            $rules = [
                'kegiatan_id' => 'required|exists:t_kegiatan,kegiatan_id',
                'status' => 'required|in:Belum,Berjalan,Selesai',
                'bukti_agenda' => 'nullable|file|mimes:pdf,doc,docx,jpg,png,jpeg|max:2048'
            ];

            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validasi Gagal',
                    'errors' => $validator->errors(),
                ], 422);
            }

            DB::beginTransaction();

            try {
                // Temukan agenda yang akan diupdate
                $agenda = AgendaModel::findOrFail($id);

                // Update status di tabel kegiatan agenda
                KegiatanAgendaModel::where('agenda_id', $id)->delete();
                KegiatanAgendaModel::create([
                    'agenda_id' => $id,
                    'kegiatan_id' => $request->kegiatan_id,
                    'status' => $request->status,
                ]);

                // Proses upload bukti agenda jika ada file
                if ($request->hasFile('bukti_agenda')) {
                    $dosenId = session('dosen_id');
                    $agendaDosenId = AgendaDosenModel::where('dosen_id', $dosenId)
                        ->where('agenda_id', $id)
                        ->firstOrFail(); // Gunakan firstOrFail untuk memudahkan debugging

                    // Hapus bukti dan dokumen lama
                    $buktiAgenda = BuktiAgendaModel::where('agenda_dosen_id', $agendaDosenId->agenda_dosen_id)
                        ->first();

                    if ($buktiAgenda) {
                        // Periksa apakah dokumen terkait ada
                        $dokumen = DokumenModel::find($buktiAgenda->dokumen_id);
                        if ($dokumen) {
                            $buktiAgenda = BuktiAgendaModel::where('agenda_dosen_id', $agendaDosenId->agenda_dosen_id)
                                ->first();
                            // $dokumen->delete(); // Hapus dokumen dari database
                            DokumenModel::where('dokumen_id', $buktiAgenda->dokumen_id)->delete();
                        }

                        $agendaDosenId = AgendaDosenModel::where('dosen_id', $dosenId)
                            ->where('agenda_id', $id)
                            ->firstOrFail();
                        BuktiAgendaModel::where('agenda_dosen_id', $agendaDosenId->agenda_dosen_id)->delete();
                        // $buktiAgenda->delete(); // Hapus data bukti agenda setelah dokumen dihapus
                    }


                    // Simpan dokumen baru
                    $cloudinaryResponse = $this->uploadBuktiAgendaToCloudinary($request->file('bukti_agenda'));

                    $dokumen = DokumenModel::create([
                        'dokumen_nama' => $cloudinaryResponse['url'],
                        'dokumen_kategori' => 'Bukti Agenda'
                    ]);

                    BuktiAgendaModel::create([
                        'agenda_dosen_id' => $agendaDosenId->agenda_dosen_id,
                        'dokumen_id' => $dokumen->dokumen_id
                    ]);
                }

                DB::commit();

                return response()->json([
                    'status' => true,
                    'message' => 'Data agenda berhasil diupdate'
                ]);
            } catch (\Exception $e) {
                DB::rollback();
                Log::error('Error in update_ajax: ' . $e->getMessage());

                return response()->json([
                    'status' => false,
                    'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
                ], 500);
            }
        }

        return redirect('/');
    }

    private function uploadBuktiAgendaToCloudinary($file)
    {
        $cloudName = 'dotz74j1p';
        $uploadPreset = 'yogjjkoh';
        $apiKey = '983354314759691';

        try {
            $response = Http::attach(
                'file',
                file_get_contents($file),
                $file->getClientOriginalName()
            )->post("https://api.cloudinary.com/v1_1/{$cloudName}/raw/upload", [
                'upload_preset' => $uploadPreset,
                'api_key' => $apiKey
            ]);

            $responseData = $response->json();

            if (!$response->successful()) {
                throw new \Exception('Cloudinary upload failed');
            }

            return $responseData;
        } catch (\Exception $e) {
            Log::error('Cloudinary Upload Error: ' . $e->getMessage());
            throw $e;
        }
    }

    public function export_excel()
    {
        try {
            // Ambil data kegiatan yang akan di-export
            $agenda = AgendaModel::select(
                't_kegiatan.kegiatan_nama',
                't_agenda.nama',
                't_agenda.tanggal_mulai',
                't_agenda.tanggal_selesai',
                't_kegiatan_agenda.status'
            )
                ->join('t_kegiatan_agenda', 't_agenda.agenda_id', '=', 't_kegiatan_agenda.agenda_id')
                ->join('t_kegiatan', 't_kegiatan_agenda.kegiatan_id', '=', 't_kegiatan.kegiatan_id')
                ->join('t_agenda_dosen', 't_agenda.agenda_id', '=', 't_agenda_dosen.agenda_id') // Perbaikan join
                ->where('t_agenda_dosen.dosen_id', session('dosen_id'))
                ->get();

            // Cek apakah data kosong
            if ($agenda->isEmpty()) {
                return redirect()->back()->with('error', 'Tidak ada data untuk di-export');
            }

            // Load library excel
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            // Header
            $sheet->setCellValue('A1', 'No');
            $sheet->setCellValue('B1', 'Nama Kegiatan');
            $sheet->setCellValue('C1', 'Agenda');
            $sheet->setCellValue('D1', 'Tanggal Mulai');
            $sheet->setCellValue('E1', 'Tanggal Selesai');
            $sheet->setCellValue('F1', 'Status');

            $sheet->getStyle('A1:F1')->getFont()->setBold(true);

            // Isi data
            $no = 1;
            $baris = 2;
            foreach ($agenda as $value) {
                $sheet->setCellValue('A' . $baris, $no);
                $sheet->setCellValue('B' . $baris, $value->kegiatan_nama);
                $sheet->setCellValue('C' . $baris, $value->nama);
                $sheet->setCellValue('D' . $baris, $value->tanggal_mulai);
                $sheet->setCellValue('E' . $baris, $value->tanggal_selesai);
                $sheet->setCellValue('F' . $baris, $value->status);

                $baris++;
                $no++;
            }

            // Auto size kolom
            foreach (range('A', 'F') as $columnID) {
                $sheet->getColumnDimension($columnID)->setAutoSize(true);
            }

            $sheet->setTitle('Data Agenda Dosen');
            $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
            $filename = 'Data_Agenda_Dosen_' . date('Y-m-d_H-i-s') . '.xlsx';

            // Header untuk download
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            header('Cache-Control: max-age=0');

            $writer->save('php://output');
            exit;
        } catch (\Exception $e) {
            // Tangani error
            return redirect()->back()->with('error', 'Gagal mengekspor data: ' . $e->getMessage());
        }
    }

    public function export_pdf()
    {
        $agenda = AgendaModel::select(
            't_kegiatan.kegiatan_nama',
            't_agenda.nama',
            't_agenda.tanggal_mulai',
            't_agenda.tanggal_selesai',
            't_kegiatan_agenda.status'
        )
            ->join('t_kegiatan_agenda', 't_agenda.agenda_id', '=', 't_kegiatan_agenda.agenda_id')
            ->join('t_kegiatan', 't_kegiatan_agenda.kegiatan_id', '=', 't_kegiatan.kegiatan_id')
            ->join('t_agenda_dosen', 't_agenda.agenda_id', '=', 't_agenda_dosen.agenda_id') // Perbaikan join
            ->where('t_agenda_dosen.dosen_id', session('dosen_id'))
            ->get();

        $pdf = Pdf::loadView('agenda_dosen.export_pdf', ['agenda' => $agenda]);
        $pdf->setPaper('a4', 'potrait');
        $pdf->setOption('isRemoteEnabled', true);
        $pdf->render();

        return $pdf->stream('Data Agenda Dosen ' . date('Y-m-d H:i:s') . '.pdf');
    }
}
