<?php

namespace App\Http\Controllers;

use App\Models\DokumenModel;
use App\Models\DosenKegiatanModel;
use App\Models\DosenModel;
use App\Models\PeriodeModel;
use App\Models\KategoriModel;
use App\Models\KegiatanModel;
use App\Models\PeranModel;
use App\Models\SuratTugasModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;

class KegiatanDosenController extends Controller
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

        return view('kegiatan_dosen.index', [
            'activeMenu' => $activeMenu,
            'breadcrumb' => $breadcrumb,
            'kegiatan' => $kegiatan,
            'kategori' => $kategori
        ]);
    }

    public function list(Request $request)
    {
        $dosenId = session('dosen_id');
        $kegiatan = KegiatanModel::with(['dosenKegiatan', 'kategori', 'periode'])
            ->whereHas('dosenKegiatan', function ($query) {
                $dosenId = session('dosen_id');
                $query->where('dosen_id', $dosenId);
            })
            ->withCount('dosenKegiatan');

        $kategori_id = $request->input('filter_kategori');
        if (!empty($kategori_id)) {
            $kegiatan->where('kategori_id', $kategori_id);
        }

        return DataTables::of($kegiatan)
            ->addIndexColumn()
            ->addColumn('kategori_nama', function ($kegiatan) {
                return $kegiatan->kategori->kategori_nama ?? 'Tidak Berkategori';
            })
            ->addColumn('periode', function ($kegiatan) {
                return $kegiatan->periode->periode ?? 'Tidak Ada Periode';
            })
            ->addColumn('jumlah_dosen', function ($kegiatan) {
                return $kegiatan->dosen_kegiatan_count;
            })
            ->addColumn('aksi', function ($kegiatan) use ($dosenId) {
                $is_pic = DosenKegiatanModel::where('kegiatan_id', $kegiatan->kegiatan_id)
                    ->where('dosen_id', $dosenId)
                    ->whereHas('peran', function ($query) {
                        $query->where('is_pic', 1);
                    })
                    ->exists(); // Gunakan exists() untuk efisiensi

                $btn = '<button onclick="modalAction(\'' . url('/kegiatan_dosen/' . $kegiatan->kegiatan_id . '/show_ajax') . '\')" class="btn btn-info btn-sm">Detail</button> ';

                if ($is_pic) {
                    $btn .= '<button onclick="modalAction(\'' . url('/kegiatan_dosen/' . $kegiatan->kegiatan_id . '/add_agenda') . '\')" class="btn btn-success btn-sm">Tambah Agenda</button> ';
                    $btn .= '<button onclick="modalAction(\'' . url('/kegiatan_dosen/' . $kegiatan->kegiatan_id . '/edit_ajax') . '\')" class="btn btn-warning btn-sm">Edit</button> ';
                    $btn .= '<button onclick="modalAction(\'' . url('/kegiatan_dosen/' . $kegiatan->kegiatan_id . '/delete_ajax') . '\')"  class="btn btn-danger btn-sm">Hapus</button> ';
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

        return view('kegiatan_dosen.create_ajax', [
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
                'periode_id' => 'required|exists:m_periode,periode_id',
                'kegiatan_nama' => 'required|string|min:3|max:255',
                'status' => 'required|in:Belum,Berjalan,Selesai',
                'skala' => 'required|in:Internal,Nasional,Internasional,Lain-Lain',
                'anggaran' => 'required|integer|min:1000',
                'deskripsi' => 'required|string|min:10|max:1000',
                'tanggal_mulai' => 'required|date',
                'tanggal_selesai' => 'required|date|after:tanggal_mulai',
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
                    'kategori_id' => 3,
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
                $bobot = $this->hitungBobot(
                    $request->skala,
                    $request->anggaran,
                    Carbon::parse($request->tanggal_mulai)->diffInDays(Carbon::parse($request->tanggal_selesai)),
                    1 // Menggunakan peran_id default 1
                );

                // Simpan dosen kegiatan
                $dosenId = session('dosen_id');
                DosenKegiatanModel::create([
                    'kegiatan_id' => $kegiatan->kegiatan_id,
                    'dosen_id' => $dosenId, // Menggunakan dosen_id dari session
                    'peran_id' => 1, // Menggunakan peran_id default 1
                    'bobot' => $bobot,
                ]);

                $notif = [
                    'user_id' => $dosenId,
                    'judul' => 'Kegiatan Baru',
                    'isi' => 'Selamat anda ditunjuk untuk mengikuti kegiatan ' . $kegiatan->kegiatan_nama . ' sebagai Penanggung Jawab',
                    'aksi' => $kegiatan->kegiatan_id . '/show_ajax',
                    'is_read' => false,
                ];
                DB::table('t_notifikasi')->insert($notif);

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

    private function hitungBobot($skala, $anggaran, $selisihHari, $peranId = 1) // Default peranId ke 1
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

        return view('kegiatan_dosen.show_ajax', compact('kegiatan', 'kategori', 'dosenKegiatan', 'dosen', 'peran'));
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
                'periode_id' => 'required|exists:m_periode,periode_id',
                'kegiatan_nama' => 'required|string|min:3|max:255',
                'status' => 'required|in:Belum,Berjalan,Selesai',
                'skala' => 'required|in:Internal,Nasional,Internasional,Lain-Lain',
                'anggaran' => 'required|integer|min:1000',
                'deskripsi' => 'required|string|min:10|max:1000',
                'tanggal_mulai' => 'required|date',
                'tanggal_selesai' => 'required|date|after:tanggal_mulai',
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
                    'kategori_id' => 3,
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
                        1
                    );

                    $dosenId = session('dosen_id');
                    DosenKegiatanModel::create([
                        'kegiatan_id' => $kegiatan->kegiatan_id,
                        'dosen_id' => $dosenId,
                        'peran_id' => 1,
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
}
