<?php

namespace App\Http\Controllers;

use App\Models\DokumenModel;
use App\Models\KegiatanModel;
use App\Models\KategoriModel;
use App\Models\DosenModel;
use App\Models\PeranModel;
use App\Models\DosenKegiatanModel;
use App\Models\SuratTugasModel;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

class KegiatanController extends Controller
{
    public function index()
    {
        $activeMenu = 'kegiatan';
        $breadcrumb = (object) [
            'title' => 'Daftar Kegiatan',
            'list' => ['Home', 'Kegiatan']
        ];

        $kegiatan = KegiatanModel::all();
        $kategori = KategoriModel::all();

        return view('kegiatan.index', [
            'activeMenu' => $activeMenu,
            'breadcrumb' => $breadcrumb,
            'kegiatan' => $kegiatan,
            'kategori' => $kategori
        ]);
    }

    public function list(Request $request)
    {
        $kegiatan = KegiatanModel::with('kategori');

        $kategori_id = $request->input('filter_kategori');
        if (!empty($kategori_id)) {
            $kegiatan->where('kategori_id', $kategori_id);
        }

        return DataTables::of($kegiatan)
            ->addIndexColumn()
            ->addColumn('kategori_nama', function ($kegiatan) {
                return $kegiatan->kategori->kategori_nama;
            })
            ->addColumn('aksi', function ($kegiatan) {
                $btn  = '<button onclick="modalAction(\'' . url('/kegiatan/' . $kegiatan->kegiatan_id . '/show_ajax') . '\')" class="btn btn-info btn-sm">Detail</button> ';
                $btn .= '<button onclick="modalAction(\'' . url('/kegiatan/' . $kegiatan->kegiatan_id . '/edit_ajax') . '\')" class="btn btn-warning btn-sm">Edit</button> ';
                $btn .= '<button onclick="modalAction(\'' . url('/kegiatan/' . $kegiatan->kegiatan_id . '/delete_ajax') . '\')"  class="btn btn-danger btn-sm">Hapus</button> ';
                return $btn;
            })
            ->rawColumns(['aksi'])
            ->make(true);
    }

    public function create_ajax()
    {
        $kategori = KategoriModel::select('kategori_id', 'kategori_nama')->get();
        $dosen = DosenModel::select('dosen_id', 'nama')->get();
        $peran = PeranModel::select('peran_id', 'peran_nama')->get();

        return view('kegiatan.create_ajax', [
            'kategori' => $kategori,
            'dosen' => $dosen,
            'peran' => $peran
        ]);
    }

    public function store_ajax(Request $request)
    {
        if ($request->ajax() || $request->wantsJson()) {
            $rules = [
                'kategori_id' => 'required|exists:m_kategori,kategori_id',
                'kegiatan_nama' => 'required|string|min:3|max:255',
                'status' => 'required|in:Belum,Berjalan,Selesai',
                'deskripsi' => 'required|string|',
                'tanggal_mulai' => 'required|date',
                'tanggal_selesai' => 'required|date',
                'dosen' => 'required|array|min:1',
                'dosen.*' => 'exists:m_dosen,dosen_id',
                'peran' => 'required|array|min:1',
                'peran.*' => 'exists:m_peran,peran_id',
                'surat_tugas' => 'nullable|file|mimes:pdf,doc,docx|max:2048'
            ];

            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validasi Gagal',
                    'errors' => $validator->errors(),
                ]);
            }

            DB::beginTransaction();

            try {
                // Simpan data kegiatan
                $kegiatan = KegiatanModel::create([
                    'kategori_id' => $request->kategori_id,
                    'kegiatan_nama' => $request->kegiatan_nama,
                    'status' => $request->status,
                    'deskripsi' => $request->deskripsi,
                    'tanggal_mulai' => $request->tanggal_mulai,
                    'tanggal_selesai' => $request->tanggal_selesai
                ]);

                // Simpan dosen kegiatan dengan peran
                foreach ($request->dosen as $index => $dosen_id) {
                    DosenKegiatanModel::create([
                        'kegiatan_id' => $kegiatan->kegiatan_id,
                        'dosen_id' => $dosen_id,
                        'peran_id' => $request->peran[$index],
                        'is_pic' => $request->peran[$index] == 1 ? true : false
                    ]);
                }

                // Upload dan simpan surat tugas jika ada
                if ($request->hasFile('surat_tugas')) {
                    // Upload surat tugas baru
                    $fileName = time() . '.' . $request->surat_tugas->getClientOriginalExtension();
                    $request->surat_tugas->storeAs('public/surat_tugas', $fileName);

                    // Buat record dokumen
                    $dokumen = DokumenModel::create([
                        'dokumen_nama' => $fileName,
                        'dokumen_kategori' => 'Surat Tugas'
                    ]);

                    // Buat record surat tugas
                    SuratTugasModel::create([
                        'kegiatan_id' => $kegiatan->kegiatan_id,
                        'dokumen_id' => $dokumen->dokumen_id
                    ]);
                }

                DB::commit();

                return response()->json([
                    'status' => true,
                    'message' => 'Data kegiatan berhasil disimpan'
                ]);
            } catch (\Exception $e) {
                DB::rollback();
                Log::error('Error in store_ajax: ' . $e->getMessage());
                Log::error($e->getTraceAsString());

                return response()->json([
                    'status' => false,
                    'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
                    'trace' => $e->getTraceAsString() // Hanya untuk debugging
                ], 500);
            }
        }
        return redirect('/');
    }

    public function show_ajax($id)
    {
        $kegiatan = KegiatanModel::select(
            't_kegiatan.*',
            'm_kategori.kategori_nama',
            'm_dokumen.dokumen_nama'
        )
            ->join('m_kategori', 't_kegiatan.kategori_id', '=', 'm_kategori.kategori_id')
            ->leftJoin('t_surat_tugas', 't_kegiatan.kegiatan_id', '=', 't_surat_tugas.kegiatan_id')
            ->leftJoin('m_dokumen', 't_surat_tugas.dokumen_id', '=', 'm_dokumen.dokumen_id')
            ->where('t_kegiatan.kegiatan_id', $id)
            ->first();

        if (!$kegiatan) {
            return response()->json(['message' => 'Kegiatan tidak ditemukan'], 404);
        }

        // Ambil dosen dan peran terkait
        $dosenKegiatan = DosenKegiatanModel::with(['dosen', 'peran'])
            ->where('kegiatan_id', $id)
            ->get();

        $kategori = KategoriModel::select('kategori_id', 'kategori_nama')->get();
        $dosen = DosenModel::select('dosen_id', 'nama')->get();
        $peran = PeranModel::select('peran_id', 'peran_nama')->get();

        return view('kegiatan.show_ajax', compact('kegiatan', 'kategori', 'dosenKegiatan', 'dosen', 'peran'));
    }

    public function edit_ajax($id)
    {
        $kegiatan = KegiatanModel::select(
            't_kegiatan.*',
            'm_kategori.kategori_nama',
            'm_dokumen.dokumen_nama' // Menambahkan kolom dokumen_nama
        )
            ->join('m_kategori', 't_kegiatan.kategori_id', '=', 'm_kategori.kategori_id')
            ->leftJoin('t_surat_tugas', 't_kegiatan.kegiatan_id', '=', 't_surat_tugas.kegiatan_id')
            ->leftJoin('m_dokumen', 't_surat_tugas.dokumen_id', '=', 'm_dokumen.dokumen_id')
            ->where('t_kegiatan.kegiatan_id', $id)
            ->first();

        if (!$kegiatan) {
            return response()->json(['message' => 'Kegiatan tidak ditemukan'], 404);
        }

        $dosenKegiatan = DosenKegiatanModel::where('kegiatan_id', $id)
            ->join('m_dosen', 't_dosen_kegiatan.dosen_id', '=', 'm_dosen.dosen_id')
            ->join('m_peran', 't_dosen_kegiatan.peran_id', '=', 'm_peran.peran_id')
            ->select('t_dosen_kegiatan.*', 'm_dosen.nama as dosen_nama', 'm_peran.peran_nama')
            ->get();

        $kategori = KategoriModel::select('kategori_id', 'kategori_nama')->get();
        $dosen = DosenModel::select('dosen_id', 'nama')->get();
        $peran = PeranModel::select('peran_id', 'peran_nama')->get();

        return view('kegiatan.edit_ajax', compact('kegiatan', 'dosenKegiatan', 'kategori', 'dosen', 'peran'));
    }

    public function update_ajax(Request $request, $id)
    {
        if ($request->ajax() || $request->wantsJson()) {
            $rules = [
                'kategori_id' => 'required|exists:m_kategori,kategori_id',
                'kegiatan_nama' => 'required|string|min:3|max:255',
                'status' => 'required|in:Belum,Berjalan,Selesai',
                'deskripsi' => 'required|string',
                'tanggal_mulai' => 'required|date',
                'tanggal_selesai' => 'required|date',
                'dosen' => 'required|array|min:1',
                'dosen.*' => 'exists:m_dosen,dosen_id',
                'peran' => 'required|array|min:1',
                'peran.*' => 'exists:m_peran,peran_id',
                'surat_tugas' => 'nullable|file|mimes:pdf,doc,docx|max:2048'
            ];

            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validasi Gagal',
                    'errors' => $validator->errors(),
                ], 422); // Tambahkan status 422 untuk validasi gagal
            }

            DB::beginTransaction();

            try {
                $kegiatan = KegiatanModel::findOrFail($id); // Gunakan findOrFail untuk otomatis menangani kesalahan

                // Update data kegiatan
                $kegiatan->update([
                    'kategori_id' => $request->kategori_id,
                    'kegiatan_nama' => $request->kegiatan_nama,
                    'status' => $request->status,
                    'deskripsi' => $request->deskripsi,
                    'tanggal_mulai' => $request->tanggal_mulai,
                    'tanggal_selesai' => $request->tanggal_selesai
                ]);

                // Hapus dosen kegiatan yang lama
                DosenKegiatanModel::where('kegiatan_id', $id)->delete();

                // Simpan dosen kegiatan yang baru
                foreach ($request->dosen as $index => $dosen_id) {
                    DosenKegiatanModel::create([
                        'kegiatan_id' => $kegiatan->kegiatan_id,
                        'dosen_id' => $dosen_id,
                        'peran_id' => $request->peran[$index],
                        'is_pic' => $request->peran[$index] == 1 ? true : false
                    ]);
                }

                // Update surat tugas jika ada
                if ($request->hasFile('surat_tugas')) {
                    // Hapus surat tugas lama
                    $oldSuratTugas = SuratTugasModel::where('kegiatan_id', $id)->first();
                    if ($oldSuratTugas) {
                        $oldDokumen = DokumenModel::find($oldSuratTugas->dokumen_id);
                        if ($oldDokumen) {
                            // Hapus file lama
                            Storage::delete('public/surat_tugas/' . $oldDokumen->dokumen_nama);
                            $oldDokumen->delete();
                        }
                        $oldSuratTugas->delete();
                    }

                    // Upload surat tugas baru
                    $fileName = time() . '.' . $request->surat_tugas->getClientOriginalExtension();
                    $request->surat_tugas->storeAs('public/surat_tugas', $fileName);

                    // Buat record dokumen baru
                    $dokumen = DokumenModel::create([
                        'dokumen_nama' => $fileName,
                        'dokumen_kategori' => 'Surat Tugas'
                    ]);

                    // Buat record surat tugas baru
                    SuratTugasModel::create([
                        'kegiatan_id' => $kegiatan->kegiatan_id,
                        'dokumen _id' => $dokumen->dokumen_id
                    ]);
                }

                DB::commit();

                return response()->json([
                    'status' => true,
                    'message' => 'Data kegiatan berhasil diupdate'
                ]);
            } catch (\Exception $e) {
                DB::rollback();
                Log::error('Error in update_ajax: ' . $e->getMessage());
                return response()->json([
                    'status' => false,
                    'message' => 'Terjadi kesalahan: ' . $e->getMessage()
                ], 500);
            }
        }
        return redirect('/');
    }

    public function confirm_ajax($id)
    {
        $kegiatan = KegiatanModel::select(
            't_kegiatan.*',
            'm_kategori.kategori_nama',
            'm_dokumen.dokumen_nama'
        )
            ->join('m_kategori', 't_kegiatan.kategori_id', '=', 'm_kategori.kategori_id')
            ->leftJoin('t_surat_tugas', 't_kegiatan.kegiatan_id', '=', 't_surat_tugas.kegiatan_id')
            ->leftJoin('m_dokumen', 't_surat_tugas.dokumen_id', '=', 'm_dokumen.dokumen_id')
            ->where('t_kegiatan.kegiatan_id', $id)
            ->first();

        if (!$kegiatan) {
            return response()->json(['message' => 'Kegiatan tidak ditemukan'], 404);
        }

        // Ambil dosen dan peran terkait
        $dosenKegiatan = DosenKegiatanModel::with(['dosen', 'peran'])
            ->where('kegiatan_id', $id)
            ->get();

        $kategori = KategoriModel::select('kategori_id', 'kategori_nama')->get();
        $dosen = DosenModel::select('dosen_id', 'nama')->get();
        $peran = PeranModel::select('peran_id', 'peran_nama')->get();

        return view('kegiatan.confirm_ajax', compact('kegiatan', 'kategori', 'dosenKegiatan', 'dosen', 'peran'));
    }

    public function delete_ajax(Request $request, $id)
    {
        if ($request->ajax() || $request->wantsJson()) {
            DB::beginTransaction();
            try {
                // Mencari kegiatan beserta dokumen terkait
                $kegiatan = KegiatanModel::select(
                    't_kegiatan.*',
                    'm_dokumen.dokumen_nama'
                )
                    ->leftJoin('t_surat_tugas', 't_kegiatan.kegiatan_id', '=', 't_surat_tugas.kegiatan_id')
                    ->leftJoin('m_dokumen', 't_surat_tugas.dokumen_id', '=', 'm_dokumen.dokumen_id')
                    ->where('t_kegiatan.kegiatan_id', $id)
                    ->first();

                if (!$kegiatan) {
                    throw new \Exception('Data tidak ditemukan');
                }

                // Hapus surat tugas dan dokumen terkait
                $suratTugas = SuratTugasModel::where('kegiatan_id', $id)->first();
                if ($suratTugas) {
                    $dokumen = DokumenModel::find($suratTugas->dokumen_id);
                    if ($dokumen) {
                        // Hapus file fisik
                        if ($dokumen->dokumen_nama) {
                            Storage::delete('public/surat_tugas/' . $dokumen->dokumen_nama);
                        }
                        $dokumen->delete();
                    }
                    $suratTugas->delete();
                }

                // Hapus relasi dosen kegiatan
                DosenKegiatanModel::where('kegiatan_id', $id)->delete();

                // Hapus kegiatan
                $kegiatan->delete();

                DB::commit();
                return response()->json([
                    'status' => true,
                    'message' => 'Data kegiatan berhasil dihapus'
                ]);
            } catch (\Exception $e) {
                DB::rollback();
                Log::error('Error in delete_ajax: ' . $e->getMessage());
                return response()->json([
                    'status' => false,
                    'message' => 'Terjadi kesalahan: ' . $e->getMessage()
                ], 500);
            }
        }
        return redirect('/');
    }

    public function export_excel()
    {
        // ambil data kegiatan yang akan di export
        $kegiatan = KegiatanModel::select(
            't_kegiatan.kegiatan_nama',
            'm_kategori.kategori_nama',
            't_kegiatan.status',
            't_kegiatan.tanggal_mulai',
            't_kegiatan.tanggal_selesai'
        )
            ->join('m_kategori', 't_kegiatan.kategori_id', '=', 'm_kategori.kategori_id')
            ->get();

        // load library excel
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->setCellValue('A1', 'No');
        $sheet->setCellValue('B1', 'Nama Kegiatan');
        $sheet->setCellValue('C1', 'Kategori');
        $sheet->setCellValue('D1', 'Status');
        $sheet->setCellValue('E1', 'Tanggal Mulai');
        $sheet->setCellValue('F1', 'Tanggal Selesai');

        $sheet->getStyle('A1:F1')->getFont()->setBold(true);

        $no = 1; // no data dimulai dari 1
        $baris = 2; // baris data dimulai dari baris ke 2
        foreach ($kegiatan as $key => $value) {
            $sheet->setCellValue('A' . $baris, $no);
            $sheet->setCellValue('B' . $baris, $value->kegiatan_nama);
            $sheet->setCellValue('C' . $baris, $value->kategori_nama);
            $sheet->setCellValue('D' . $baris, $value->status);
            $sheet->setCellValue('E' . $baris, $value->tanggal_mulai);
            $sheet->setCellValue('F' . $baris, $value->tanggal_selesai);
            $baris++;
            $no++;
        }

        foreach (range('A', 'F') as $columnID) {
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
        }

        $sheet->setTitle('Data Kegiatan');
        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
        $filename = 'Data Kegiatan ' . date('Y-m-d H:i:s') . '.xlsx';

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
        $kegiatan = KegiatanModel::select(
            't_kegiatan.kegiatan_nama',
            'm_kategori.kategori_nama',
            't_kegiatan.status',
            't_kegiatan.tanggal_mulai',
            't_kegiatan.tanggal_selesai'
        )
            ->join('m_kategori', 't_kegiatan.kategori_id', '=', 'm_kategori.kategori_id')
            ->get();

        $pdf = Pdf::loadView('kegiatan.export_pdf', ['kegiatan' => $kegiatan]);
        $pdf->setPaper('a4', 'potrait');
        $pdf->setOption('isRemoteEnabled', true);
        $pdf->render();

        return $pdf->stream('Data Kegiatan ' . date('Y-m-d H:i:s') . '.pdf');
    }

    public function export_draft_surat_tugas($id)
    {
        $kegiatan = KegiatanModel::select(
            't_kegiatan.kegiatan_nama',
            'm_kategori.kategori_nama',
            't_kegiatan.tanggal_mulai',
            't_kegiatan.tanggal_selesai'
        )
            ->join('m_kategori', 't_kegiatan.kategori_id', '=', 'm_kategori.kategori_id')
            ->where('t_kegiatan.kegiatan_id', $id)
            ->first();

        if (!$kegiatan) {
            return response()->json(['message' => 'Kegiatan tidak ditemukan'], 404);
        }

        $dosenKegiatan = DosenKegiatanModel::with(['dosen', 'peran'])
            ->where('kegiatan_id', $id)
            ->get();

        $pdf = Pdf::loadView('kegiatan.export_draft_surat_tugas', [
            'kegiatan' => $kegiatan,
            'dosenKegiatan' => $dosenKegiatan
        ]);
        $pdf->setPaper('a4', 'potrait');
        $pdf->setOption('isRemoteEnabled', true);
        $pdf->render();

        // Membuat nama file yang aman untuk digunakan
        $safeFileName = preg_replace('/[^a-z0-9]+/', '-', strtolower($kegiatan->kegiatan_nama));
        $fileName = 'Draft Surat Tugas - ' . $safeFileName . ' - ' . date('Y-m-d H:i:s') . '.pdf';

        return $pdf->stream($fileName);
    }
}
