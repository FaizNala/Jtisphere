<?php

namespace App\Http\Controllers;

use App\Models\AgendaDosenModel;
use App\Models\AgendaModel;
use App\Models\DosenModel;
use App\Models\KategoriModel;
use App\Models\KegiatanAgendaModel;
use App\Models\KegiatanModel;
use App\Models\PeranModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;

class AgendaController extends Controller
{
    public function index()
    {
        $activeMenu = 'agenda';
        $breadcrumb = (object) [
            'title' => 'Daftar Agenda Kegiatan',
            'list' => ['Home', 'Agenda Kegiatan']
        ];

        $kegiatan = KegiatanModel::all();
        $kategori = KategoriModel::all();

        return view('agenda.index', [
            'activeMenu' => $activeMenu,
            'breadcrumb' => $breadcrumb,
            'kegiatan' => $kegiatan,
            'kategori' => $kategori
        ]);
    }

    public function list(Request $request)
    {
        // Ambil dosen_id dari session
        $dosenId = session('dosen_id');

        // Mulai query untuk mendapatkan kegiatan
        $kegiatan = DB::table('t_kegiatan as k')
            ->join('t_dosen_kegiatan as dk', 'k.kegiatan_id', '=', 'dk.kegiatan_id')
            ->join('m_kategori as kt', 'k.kategori_id', '=', 'kt.kategori_id')
            ->join('m_dosen as d', 'dk.dosen_id', '=', 'd.dosen_id')
            ->select('k.*', 'dk.*', 'kt.kategori_nama', 'd.*')
            ->where('dk.is_pic', 1)
            ->where('dk.dosen_id', $dosenId);

        // Filter berdasarkan kategori jika ada
        $kategori_id = $request->input('filter_kategori');
        if (!empty($kategori_id)) {
            $kegiatan->where('k.kategori_id', $kategori_id);
        }

        // Ambil hasil query
        return DataTables::of($kegiatan)
            ->addIndexColumn()
            ->addColumn('kategori_nama', function ($kegiatan) {
                return $kegiatan->kategori_nama;
            })
            ->addColumn('aksi', function ($kegiatan) use ($dosenId) {
                // Periksa apakah dosen adalah PIC untuk kegiatan spesifik ini
                $is_pic = DB::table('t_dosen_kegiatan')
                    ->where('kegiatan_id', $kegiatan->kegiatan_id)
                    ->where('dosen_id', $dosenId)
                    ->where('is_pic', 1)
                    ->exists();


                if ($is_pic) {
                    $btn = '<button onclick="modalAction(\'' . url('/agenda/' . $kegiatan->kegiatan_id . '/show_ajax') . '\')" class="btn btn-info btn-sm mr-1">Detail</button> ';
                    $btn .= '<button onclick="modalAction(\'' . url('/agenda/' . $kegiatan->kegiatan_id . '/edit_ajax') . '\')" class="btn btn-warning btn-sm mr-1">Edit</button> ';
                    $btn .= '<button onclick="modalAction(\'' . url('/agenda/' . $kegiatan->kegiatan_id . '/delete_ajax') . '\')" class="btn btn-danger btn-sm">Hapus</button> ';
                }

                return $btn;
            })
            ->rawColumns(['aksi'])
            ->make(true);
    }

    public function create_ajax()
    {
        $dosenId = session('dosen_id');
        $kegiatan = DB::table('t_kegiatan as k')
            ->join('t_dosen_kegiatan as dk', 'k.kegiatan_id', '=', 'dk.kegiatan_id')
            ->join('m_kategori as kt', 'k.kategori_id', '=', 'kt.kategori_id')
            ->join('m_dosen as d', 'dk.dosen_id', '=', 'd.dosen_id')
            ->select('k.*', 'dk.*', 'kt.kategori_nama', 'd.*')
            ->where('dk.is_pic', 1)
            ->where('dk.dosen_id', $dosenId)
            ->get();


            $dosen = DB::table('m_dosen as d')
            ->join('t_dosen_kegiatan as dk', 'dk.dosen_id', '=', 'd.dosen_id')
            ->join('t_kegiatan as k', 'k.kegiatan_id', '=', 'dk.kegiatan_id')
            ->select('d.*', 'dk.*')  // Ubah select
            ->where('dk.is_pic', 1)
            ->where('dk.dosen_id', $dosenId)
            ->get();

        $peran = PeranModel::select('peran_id', 'peran_nama')->get();

        return view('agenda.create_ajax', [
            'kegiatan' => $kegiatan,
            'dosen' => $dosen,
            'peran' => $peran
        ]);
    }

    public function store_ajax(Request $request)
    {
        if ($request->ajax() || $request->wantsJson()) {
            // Aturan validasi
            $rules = [
                'kegiatan_nama' => 'required|exists:t_kegiatan,kegiatan_id',
                'nama' => 'required|string|min:3|max:255',
                'status' => 'required|in:Belum,Berjalan,Selesai',
                'tanggal_mulai' => 'required|date',
                'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
                'dosen' => 'required|array|min:1',
                'dosen.*' => 'exists:m_dosen,dosen_id',
                'bobot' => 'required|array|min:1',
                'bobot.*' => 'numeric|min:0',
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
                // Simpan data agenda
                $agenda = AgendaModel::create([
                    'nama' => $request->nama,
                    'tanggal_mulai' => $request->tanggal_mulai,
                    'tanggal_selesai' => $request->tanggal_selesai,
                ]);

                // Simpan data kegiatan agenda
                KegiatanAgendaModel::create([
                    'agenda_id' => $agenda->agenda_id,
                    'kegiatan_id' => $request->kegiatan_nama,
                    'status' => $request->status,
                ]);

                // Simpan dosen dan bobot
                foreach ($request->dosen as $index => $dosen_id) {
                    AgendaDosenModel::create([
                        'agenda_id' => $agenda->agenda_id,
                        'dosen_id' => $dosen_id,
                        'bobot' => $request->bobot[$index],
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

    // public function store_ajax(Request $request, $kegiatan_id)
    // {
    //     if ($request->ajax() || $request->wantsJson()) {
    //         // Aturan validasi
    //         $rules = [
    //             'nama' => 'required|array|min:1', // Menjadi array untuk menangani beberapa agenda
    //             'nama.*' => 'string|min:3|max:255', // Validasi untuk setiap nama agenda
    //             'status' => 'required|array|min:1',
    //             'status.*' => 'in:Belum,Berjalan,Selesai', // Validasi untuk setiap status
    //             'tanggal_mulai' => 'required|array|min:1',
    //             'tanggal_mulai.*' => 'date',
    //             'tanggal_selesai' => 'required|array|min:1',
    //             'tanggal_selesai.*' => 'date|after_or_equal:tanggal_mulai.*', // Validasi untuk setiap tanggal selesai
    //             'dosen' => 'required|array|min:1',
    //             'dosen.*' => 'exists:m_dosen,dosen_id',
    //             'bobot' => 'required|array|min:1',
    //             'bobot.*' => 'numeric|min:0',
    //         ];

    //         $validator = Validator::make($request->all(), $rules);

    //         if ($validator->fails()) {
    //             return response()->json([
    //                 'status' => false,
    //                 'message' => 'Validasi Gagal',
    //                 'errors' => $validator->errors(),
    //             ]);
    //         }

    //         DB::beginTransaction();

    //         try {
    //             // Loop untuk menyimpan setiap agenda
    //             foreach ($request->nama as $index => $agenda_nama) {
    //                 // Simpan data agenda
    //                 $agenda = AgendaModel::create([
    //                     'nama' => $agenda_nama,
    //                     'tanggal_mulai' => $request->tanggal_mulai[$index],
    //                     'tanggal_selesai' => $request->tanggal_selesai[$index],
    //                     'progress_persen' => 0,
    //                     'progress_deskripsi' => ''
    //                 ]);

    //                 // Simpan data kegiatan agenda
    //                 KegiatanAgendaModel::create([
    //                     'agenda_id' => $agenda->agenda_id,
    //                     'kegiatan_id' => $kegiatan_id, // Menggunakan ID kegiatan dari parameter
    //                     'status' => $request->status[$index],
    //                 ]);

    //                 // Simpan dosen dan bobot
    //                 foreach ($request->dosen as $dosen_index => $dosen_id) {
    //                     AgendaDosenModel::create([
    //                         'agenda_id' => $agenda->agenda_id,
    //                         'dosen_id' => $dosen_id,
    //                         'bobot' => $request->bobot[$dosen_index],
    //                     ]);
    //                 }
    //             }

    //             DB::commit();

    //             return response()->json([
    //                 'status' => true,
    //                 'message' => 'Data kegiatan berhasil disimpan'
    //             ]);
    //         } catch (\Exception $e) {
    //             DB::rollback();
    //             Log::error('Error in store_ajax: ' . $e->getMessage());
    //             Log::error($e->getTraceAsString());
    //             Log::info('Request received for store_ajax', $request->all());

    //             return response()->json([
    //                 'status' => false,
    //                 'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
    //                 'trace' => $e->getTraceAsString() // Hanya untuk debugging
    //             ], 500);
    //         }
    //     }
    //     return redirect('/');
    // }


    // public function edit_ajax($id)
    // {
    //     $kegiatan = KegiatanModel::find($id);

    //     $dosen = DB::table('m_dosen as d')
    //         ->join('t_dosen_kegiatan as dk', 'dk.dosen_id', '=', 'd.dosen_id')
    //         ->join('t_kegiatan as k', 'k.kegiatan_id', '=', 'dk.kegiatan_id')
    //         ->where('k.kegiatan_id', $id)
    //         ->select('d.*')
    //         ->get();

    //     $peran = DB::table('m_peran as p')
    //         ->join('t_dosen_kegiatan as dk', 'dk.peran_id', '=', 'p.peran_id')
    //         ->join('m_dosen as d', 'dk.dosen_id', '=', 'd.dosen_id')
    //         ->join('t_kegiatan as k', 'k.kegiatan_id', '=', 'dk.kegiatan_id')
    //         ->where('k.kegiatan_id', $id)
    //         ->select('p.*')
    //         ->get();

    //     $agenda = DB::table('t_agenda as a')
    //         ->join('t_kegiatan_agenda as ka', 'ka.agenda_id', '=', 'a.agenda_id')
    //         ->join('t_kegiatan as k', 'ka.kegiatan_id', '=', 'k.kegiatan_id')
    //         ->where('k.kegiatan_id', $id)
    //         ->select('a.*', 'ka.status as kegiatan_agenda_status')
    //         ->get();

    //     // Tambahkan informasi dosen untuk setiap agenda
    //     $agendaWithDetails = $agenda->map(function ($item) {
    //         $dosenAgenda = DB::table('t_agenda_dosen as ad')
    //             ->join('m_dosen as d', 'ad.dosen_id', '=', 'd.dosen_id')
    //             ->where('ad.agenda_id', $item->agenda_id)
    //             ->select('d.*', 'ad.bobot')
    //             ->get();

    //         $item->agendaDosen = $dosenAgenda;
    //         $item->kegiatanAgenda = (object)[
    //             'status' => $item->kegiatan_agenda_status
    //         ];

    //         return $item;
    //     });

    //     return view('agenda.edit_ajax', [
    //         'kegiatan' => $kegiatan,
    //         'dosen' => $dosen,
    //         'peran' => $peran,
    //         'agenda' => $agendaWithDetails
    //     ]);
    // }

    // public function update_ajax(Request $request, $kegiatan_id)
    // {
    //     if ($request->ajax() || $request->wantsJson()) {
    //         // Aturan validasi
    //         $rules = [
    //             'agenda_id' => 'required|array|min:1', // Tambahkan validasi untuk ID agenda
    //             'agenda_id.*' => 'exists:t_agenda,agenda_id', // Pastikan agenda yang akan diupdate ada di database
    //             'nama' => 'required|array|min:1',
    //             'nama.*' => 'string|min:3|max:255',
    //             'status' => 'required|array|min:1',
    //             'status.*' => 'in:Belum,Berjalan,Selesai',
    //             'tanggal_mulai' => 'required|array|min:1',
    //             'tanggal_mulai.*' => 'date',
    //             'tanggal_selesai' => 'required|array|min:1',
    //             'tanggal_selesai.*' => 'date|after_or_equal:tanggal_mulai.*',
    //             'dosen' => 'required|array|min:1',
    //             'dosen.*' => 'exists:m_dosen,dosen_id',
    //             'bobot' => 'required|array|min:1',
    //             'bobot.*' => 'numeric|min:0',
    //         ];

    //         $validator = Validator::make($request->all(), $rules);

    //         if ($validator->fails()) {
    //             return response()->json([
    //                 'status' => false,
    //                 'message' => 'Validasi Gagal',
    //                 'errors' => $validator->errors(),
    //             ]);
    //         }

    //         if ($request->ajax() || $request->wantsJson()) {
    //             DB::beginTransaction();

    //             try {
    //                 // Loop untuk mengupdate setiap agenda
    //                 foreach ($request->agenda_id as $index => $agenda_id) {
    //                     // Cari agenda yang akan diupdate
    //                     $agenda = AgendaModel::findOrFail($agenda_id);

    //                     // Update data agenda
    //                     $agenda->update([
    //                         'nama' => $request->nama[$index],
    //                         'tanggal_mulai' => $request->tanggal_mulai[$index],
    //                         'tanggal_selesai' => $request->tanggal_selesai[$index],
    //                     ]);

    //                     // Hapus dosen lama untuk agenda ini
    //                     AgendaDosenModel::where('agenda_id', $agenda->agenda_id)->delete();

    //                     // Proses penyimpanan dosen
    //                     foreach ($request->dosen as $dosen_index => $dosen_id) {
    //                         AgendaDosenModel::create([
    //                             'agenda_id' => $agenda->agenda_id,
    //                             'dosen_id' => $dosen_id,
    //                             'bobot' => $request->bobot[$dosen_index],
    //                         ]);
    //                     }
    //                 }

    //                 DB::commit();

    //                 return response()->json([
    //                     'status' => true,
    //                     'message' => 'Data kegiatan berhasil diupdate'
    //                 ]);
    //             } catch (\Exception $e) {
    //                 DB::rollback();
    //                 Log::error('Error in update_ajax', [
    //                     'message' => $e->getMessage(),
    //                     'trace' => $e->getTraceAsString(),
    //                     'request_data' => $request->all()
    //                 ]);

    //                 return response()->json([
    //                     'status' => false,
    //                     'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
    //                     'trace' => $e->getTraceAsString()
    //                 ], 500);
    //             }
    //         }
    //         return redirect('/');
    //     }
    // }
}
