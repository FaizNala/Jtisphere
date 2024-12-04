<?php

namespace App\Http\Controllers;

use App\Models\DokumenModel;
use App\Models\KegiatanModel;
use App\Models\KategoriModel;
use App\Models\DosenModel;
use App\Models\PeranModel;
use App\Models\DosenKegiatanModel;
use App\Models\PeriodeModel;
use App\Models\SuratTugasModel;
use App\Models\NotifikasiModel;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

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
        $kegiatan = KegiatanModel::with(['kategori', 'periode'])
            ->withCount('dosenKegiatan'); // Menghitung jumlah dosen yang terkait untuk setiap kegiatan

        $kategori_id = $request->input('filter_kategori');
        if (!empty($kategori_id)) {
            $kegiatan->where('kategori_id', $kategori_id);
        }

        return DataTables::of($kegiatan)
            ->addIndexColumn()
            ->addColumn('kategori_nama', function ($kegiatan) {
                return $kegiatan->kategori->kategori_nama;
            })
            ->addColumn('periode', function ($kegiatan) {
                return $kegiatan->periode->periode;
            })
            ->addColumn('jumlah_dosen', function ($kegiatan) {
                return $kegiatan->dosen_kegiatan_count; // Menampilkan jumlah dosen
            })
            ->addColumn('aksi', function ($kegiatan) {
                $btn  = '<button onclick="modalAction(\'' . url('/kegiatan/' . $kegiatan->kegiatan_id . '/show_ajax') . '\')" class="btn btn-info btn-sm">Detail</button> ';
                $btn .= '<button onclick="modalAction(\'' . url('/kegiatan/' . $kegiatan->kegiatan_id . '/edit_ajax') . '\')" class="btn btn-warning btn-sm">Edit</button> ';
                $currentLevelId = session('current_level_id');
                $userRole = optional(
                    optional(Auth::user()->dosen->dosenLevel->where('level_id', $currentLevelId)->first())->level,
                )->level_kode;
                if ($userRole == 'ADM') {
                    $btn .= '<button onclick="modalAction(\'' . url('/kegiatan/' . $kegiatan->kegiatan_id . '/delete_ajax') . '\')"  class="btn btn-danger btn-sm">Hapus</button> ';
                }
                return $btn;
            })
            ->rawColumns(['aksi'])
            ->make(true);
    }

    public function create_ajax()
    {
        $kategori = KategoriModel::select('kategori_id', 'kategori_nama')->get();
        $periode = PeriodeModel::select('periode_id', 'periode')->where('status', 'Aktif')->get();
        $dosen = DosenModel::select('dosen_id', 'nama')->get();
        $peran = PeranModel::select('peran_id', 'peran_nama')->get();

        return view('kegiatan.create_ajax', [
            'kategori' => $kategori,
            'periode' => $periode,
            'dosen' => $dosen,
            'peran' => $peran
        ]);
    }

    public function store_ajax(Request $request)
    {
        if ($request->ajax() || $request->wantsJson()) {
            $rules = [
                'kategori_id' => 'required|exists:m_kategori,kategori_id',
                'periode_id' => 'required|exists:m_periode,periode_id',
                'kegiatan_nama' => 'required|string|min:3|max:255',
                'status' => 'required|in:Belum,Berjalan,Selesai',
                'skala' => 'required|in:Internal,Nasional,Internasional,Lain-Lain',
                'anggaran' => 'required|integer|min:1000',
                'deskripsi' => 'required|string|min:10|max:1000',
                'tanggal_mulai' => 'required|date',
                'tanggal_selesai' => 'required|date|after:tanggal_mulai',
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
                    'msgField' => $validator->errors()
                ], 422);
            }

            DB::beginTransaction();
            try {
                // Simpan data kegiatan
                $kegiatan = KegiatanModel::create([
                    'kategori_id' => $request->kategori_id,
                    'periode_id' => $request->periode_id,
                    'kegiatan_nama' => $request->kegiatan_nama,
                    'skala' => $request->skala,
                    'anggaran' => $request->anggaran,
                    'status' => $request->status,
                    'deskripsi' => $request->deskripsi,
                    'tanggal_mulai' => $request->tanggal_mulai,
                    'tanggal_selesai' => $request->tanggal_selesai
                ]);

                // Proses perhitungan bobot yang lebih jelas
                foreach ($request->dosen as $index => $dosen_id) {
                    $bobot = $this->hitungBobot(
                        $request->skala,
                        $request->anggaran,
                        Carbon::parse($request->tanggal_mulai)->diffInDays(Carbon::parse($request->tanggal_selesai)),
                        $request->peran[$index]
                    );

                    DosenKegiatanModel::create([
                        'kegiatan_id' => $kegiatan->kegiatan_id,
                        'dosen_id' => $dosen_id,
                        'peran_id' => $request->peran[$index],
                        'bobot' => $bobot,
                    ]);

                    $notif = [
                        'user_id' => $dosen_id,
                        'judul' => 'Kegiatan Baru',
                        'isi' => 'Selamat anda ditunjuk untuk mengikuti kegiatan ' . $kegiatan->kegiatan_nama . ' sebagai ' . PeranModel::find($request->peran[$index])->peran_nama,
                        'aksi' => $kegiatan->kegiatan_id . '/show_ajax',
                        'is_read' => false,
                    ];
                    DB::table('t_notifikasi')->insert($notif);

                    // NotifikasiModel::create([
                    //     'kegiatan_id' => $kegiatan->kegiatan_id,
                    //     'user_id' => $dosen_id,
                    //     'judul' => 'Kegiatan Baru',
                    //     'isi' => 'Selamat anda ditunjuk untuk mengikuti kegiatan ' . $kegiatan->kegiatan_nama,
                    //     // 'aksi' => $kegiatan->kegiatan_id . '/show_ajax',
                    //     // 'is_read' => 0,
                    // ]);

                }

                // Upload surat tugas
                if ($request->hasFile('surat_tugas')) {
                    $fileName = time() . '.' . $request->surat_tugas->getClientOriginalExtension();
                    $request->surat_tugas->storeAs('public/surat_tugas', $fileName);

                    $dokumen = DokumenModel::create([
                        'dokumen_nama' => $fileName,
                        'dokumen_kategori' => 'Surat Tugas'
                    ]);

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

                return response()->json([
                    'status' => false,
                    'message' => 'Terjadi kesalahan: ' . $e->getMessage()
                ], 500);
            }
        }
        return redirect('/');
    }

    private function hitungBobot($skala, $anggaran, $selisihHari, $peranId)
    {
        // Bobot Skala
        $b1 = match ($skala) {
            'Internal' => 2,
            'Nasional' => 3,
            'Internasional' => 4,
            default => 1
        };

        // Bobot Anggaran
        $b2 = match (true) {
            $anggaran >= 1000000000 => 4,
            $anggaran >= 100000000 => 3,
            $anggaran >= 10000000 => 2,
            default => 1
        };

        // Bobot Waktu
        $b3 = match (true) {
            $selisihHari > 365 => 5,
            $selisihHari >= 274 => 4,
            $selisihHari >= 183 => 3,
            $selisihHari >= 91 => 2,
            default => 1
        };

        // Bobot Peran
        $b4 = match ($peranId) {
            1 => 5,
            2, 3 => 3,
            default => 1
        };

        // Hitung rata-rata
        return round(($b1 + $b2 + $b3 + $b4) / 4);
    }

    // Metode tambahan untuk perhitungan bobot
    // private function hitungBobot($skala, $anggaran, $selisihHari, $peranId)
    // {
    //     $b1 = $this->hitungBobotSkala($skala);
    //     $b2 = $this->hitungBobotAnggaran($anggaran);
    //     $b3 = $this->hitungBobotWaktu($selisihHari);
    //     $b4 = $this->hitungBobotPeran($peranId);

    //     return round(($b1 + $b2 + $b3 + $b4) / 4);
    // }

    // private function hitungBobotSkala($skala)
    // {
    //     switch ($skala) {
    //         case 'Internal':
    //             return 2;
    //         case 'Nasional':
    //             return 3;
    //         case 'Internasional':
    //             return 4;
    //         default:
    //             return 1;
    //     }
    // }

    // private function hitungBobotAnggaran($anggaran)
    // {
    //     if ($anggaran >= 1000000000) return 4;
    //     if ($anggaran >= 100000000) return 3;
    //     if ($anggaran >= 10000000) return 2;
    //     return 1;
    // }

    // private function hitungBobotWaktu($selisihHari)
    // {
    //     if ($selisihHari > 365) return 5;
    //     if ($selisihHari >= 274) return 4;
    //     if ($selisihHari >= 183) return 3;
    //     if ($selisihHari >= 91) return 2;
    //     return 1;
    // }

    // private function hitungBobotPeran($peranId)
    // {
    //     switch ($peranId) {
    //         case 1:
    //             return 5;
    //         case 2:
    //             return 3;
    //         case 3:
    //             return 3;
    //         default:
    //             return 1;
    //     }
    // }

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
        $periode = PeriodeModel::select('periode_id', 'periode')->where('status', 'Aktif')->get(); // Menambahkan periode
        $dosen = DosenModel::select('dosen_id', 'nama')->get();
        $peran = PeranModel::select('peran_id', 'peran_nama')->get();

        return view('kegiatan.edit_ajax', compact('kegiatan', 'dosenKegiatan', 'kategori', 'periode', 'dosen', 'peran'));
    }

    public function update_ajax(Request $request, $id)
    {
        if ($request->ajax() || $request->wantsJson()) {
            $rules = [
                'kategori_id' => 'required|exists:m_kategori,kategori_id',
                'periode_id' => 'required|exists:m_periode,periode_id',
                'kegiatan_nama' => 'required|string|min:3|max:255',
                'status' => 'required|in:Belum,Berjalan,Selesai',
                'skala' => 'required|in:Internal,Nasional,Internasional,Lain-Lain',
                'anggaran' => 'required|integer|min:1000',
                'deskripsi' => 'required|string|min:10|max:1000',
                'tanggal_mulai' => 'required|date',
                'tanggal_selesai' => 'required|date|after:tanggal_mulai',
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
                ], 422);
            }

            DB::beginTransaction();

            try {
                $kegiatan = KegiatanModel::findOrFail($id);

                // Update data kegiatan
                $kegiatan->update([
                    'kategori_id' => $request->kategori_id,
                    'periode_id' => $request->periode_id,
                    'kegiatan_nama' => $request->kegiatan_nama,
                    'skala' => $request->skala,
                    'anggaran' => $request->anggaran,
                    'status' => $request->status,
                    'deskripsi' => $request->deskripsi,
                    'tanggal_mulai' => $request->tanggal_mulai,
                    'tanggal_selesai' => $request->tanggal_selesai
                ]);

                // Hapus dosen kegiatan yang lama
                DosenKegiatanModel::where('kegiatan_id', $id)->delete();

                // Simpan dosen kegiatan yang baru
                foreach ($request->dosen as $index => $dosen_id) {
                    $bobot = $this->hitungBobot(
                        $request->skala,
                        $request->anggaran,
                        Carbon::parse($request->tanggal_mulai)->diffInDays(Carbon::parse($request->tanggal_selesai)),
                        $request->peran[$index]
                    );

                    DosenKegiatanModel::create([
                        'kegiatan_id' => $kegiatan->kegiatan_id,
                        'dosen_id' => $dosen_id,
                        'peran_id' => $request->peran[$index],
                        'bobot' => $bobot,
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
                        'dokumen_id' => $dokumen->dokumen_id
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
        $periode = PeriodeModel::select('periode_id', 'periode')->where('status', 'Aktif')->get(); // Menambahkan periode
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
            't_kegiatan.tanggal_selesai',
            'm_periode.periode',
            DB::raw('count(t_dosen_kegiatan.dosen_id) as jumlah_dosen') // Menggunakan DB::raw untuk alias
        )
            ->join('m_kategori', 't_kegiatan.kategori_id', '=', 'm_kategori.kategori_id')
            ->join('m_periode', 't_kegiatan.periode_id', '=', 'm_periode.periode_id')
            ->leftJoin('t_dosen_kegiatan', 't_kegiatan.kegiatan_id', '=', 't_dosen_kegiatan.kegiatan_id') // Perbaikan alias
            ->groupBy(
                't_kegiatan.kegiatan_nama',
                'm_kategori.kategori_nama',
                't_kegiatan.status',
                't_kegiatan.tanggal_mulai',
                't_kegiatan.tanggal_selesai',
                'm_periode.periode'
            )
            ->get();

        // load library excel
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->setCellValue('A1', 'No');
        $sheet->setCellValue('B1', 'Nama Kegiatan');
        $sheet->setCellValue('C1', 'Kategori');
        $sheet->setCellValue('D1', 'Periode');
        $sheet->setCellValue('E1', 'Status');
        $sheet->setCellValue('F1', 'Tanggal Mulai');
        $sheet->setCellValue('G1', 'Tanggal Selesai');
        $sheet->setCellValue('H1', 'Jumlah Dosen');

        $sheet->getStyle('A1:H1')->getFont()->setBold(true);

        $no = 1; // no data dimulai dari 1
        $baris = 2; // baris data dimulai dari baris ke 2
        foreach ($kegiatan as $key => $value) {
            $sheet->setCellValue('A' . $baris, $no);
            $sheet->setCellValue('B' . $baris, $value->kegiatan_nama);
            $sheet->setCellValue('C' . $baris, $value->kategori_nama);
            $sheet->setCellValue('D' . $baris, $value->periode);
            $sheet->setCellValue('E' . $baris, $value->skala);
            $sheet->setCellValue('F' . $baris, $value->jumlah_dosen);
            $sheet->setCellValue('G' . $baris, $value->status);
            $sheet->setCellValue('H' . $baris, $value->tanggal_mulai);
            $sheet->setCellValue('I' . $baris, $value->tanggal_selesai);
            $baris++;
            $no++;
        }

        foreach (range('A', 'I') as $columnID) {
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
            't_kegiatan.tanggal_selesai',
            'm_periode.periode',
            DB::raw('count(t_dosen_kegiatan.dosen_id) as jumlah_dosen') // Menggunakan DB::raw untuk alias
        )
            ->join('m_kategori', 't_kegiatan.kategori_id', '=', 'm_kategori.kategori_id')
            ->join('m_periode', 't_kegiatan.periode_id', '=', 'm_periode.periode_id')
            ->leftJoin('t_dosen_kegiatan', 't_kegiatan.kegiatan_id', '=', 't_dosen_kegiatan.kegiatan_id') // Perbaikan alias
            ->groupBy(
                't_kegiatan.kegiatan_nama',
                'm_kategori.kategori_nama',
                't_kegiatan.status',
                't_kegiatan.tanggal_mulai',
                't_kegiatan.tanggal_selesai',
                'm_periode.periode'
            )
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
            't_kegiatan.status',
            't_kegiatan.tanggal_mulai',
            't_kegiatan.tanggal_selesai',
            'm_periode.periode'
        )
            ->join('m_kategori', 't_kegiatan.kategori_id', '=', 'm_kategori.kategori_id')
            ->join('m_periode', 't_kegiatan.periode_id', '=', 'm_periode.periode_id')
            ->where('t_kegiatan.kegiatan_id', $id)
            ->first(); // Ganti get() dengan first()

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
