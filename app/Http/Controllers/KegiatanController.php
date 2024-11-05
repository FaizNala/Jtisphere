<?php

namespace App\Http\Controllers;

use App\Models\DokumenModel;
use App\Models\KegiatanModel;
use App\Models\KategoriModel;
use App\Models\DosenModel;
use App\Models\PeranModel;
use App\Models\DosenKegiatanModel;
use App\Models\SuratTugasModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

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
                    'deskripsi' => $request->deskripsi
                ]);

                // Simpan dosen kegiatan dengan peran
                foreach ($request->dosen as $index => $dosen_id) {
                    DosenKegiatanModel::create([
                        'kegiatan_id' => $kegiatan->kegiatan_id,
                        'dosen_id' => $dosen_id,
                        'peran_id' => $request->peran[$index]
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
                    'deskripsi' => $request->deskripsi
                ]);

                // Hapus dosen kegiatan yang lama
                DosenKegiatanModel::where('kegiatan_id', $id)->delete();

                // Simpan dosen kegiatan yang baru
                foreach ($request->dosen as $index => $dosen_id) {
                    DosenKegiatanModel::create([
                        'kegiatan_id' => $kegiatan->kegiatan_id,
                        'dosen_id' => $dosen_id,
                        'peran_id' => $request->peran[$index]
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

    public function download_surat_tugas($id)
    {
        $kegiatan = KegiatanModel::findOrFail($id);

        if ($kegiatan->dokumen_nama) {
            $path = storage_path('app/public/surat_tugas/');

            if (file_exists($path)) {
                return response()->download($path, $kegiatan->dokumen_nama);
            }
        }

        return back()->with('error', 'File tidak ditemukan.');
    }
}
