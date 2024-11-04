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

        return view('kegiatan.index', [
            'activeMenu' => $activeMenu,
            'breadcrumb' => $breadcrumb,
            'kegiatan' => $kegiatan
        ]);
    }

    public function list(Request $request)
    {
        $kegiatan = KegiatanModel::with('kategori');

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
}
