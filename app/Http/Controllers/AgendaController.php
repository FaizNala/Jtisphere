<?php

namespace App\Http\Controllers;

use App\Models\AgendaDosenModel;
use App\Models\AgendaModel;
use App\Models\DosenModel;
use App\Models\KategoriModel;
use App\Models\KegiatanAgendaModel;
use App\Models\KegiatanModel;
use App\Models\DosenKegiatanModel;
use App\Models\PeranModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;

class AgendaController extends Controller
{
    public function add_agenda($id)
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

        $agenda = AgendaModel::select(
            't_agenda.agenda_id',
            't_agenda.nama',
            't_kegiatan_agenda.kegiatan_id',
            't_kegiatan_agenda.status',
            DB::raw('COUNT(t_agenda_dosen.dosen_id) as jumlah_dosen')
        )
            ->join('t_kegiatan_agenda', 't_agenda.agenda_id', '=', 't_kegiatan_agenda.agenda_id')
            ->join('t_kegiatan', 't_kegiatan.kegiatan_id', '=', 't_kegiatan_agenda.kegiatan_id')
            ->leftJoin('t_agenda_dosen', 't_agenda_dosen.agenda_id', '=', 't_agenda.agenda_id')
            ->where('t_kegiatan_agenda.kegiatan_id', $id)
            ->groupBy(
                't_agenda.agenda_id',
                't_agenda.nama',
                't_kegiatan_agenda.kegiatan_id',
                't_kegiatan_agenda.status'
            )
            ->get();
        

        // Ambil dosen dan peran terkait
        $dosenKegiatan = DosenKegiatanModel::with(['dosen', 'peran'])
            ->where('kegiatan_id', $id)
            ->get();

        $kategori = KategoriModel::select('kategori_id', 'kategori_nama')->get();
        $dosen = DosenModel::select('dosen_id', 'nama')->get();
        $peran = PeranModel::select('peran_id', 'peran_nama')->get();

        return view('kegiatan_dosen.add_agenda', compact('kegiatan', 'kategori', 'dosenKegiatan', 'dosen', 'peran', 'agenda'));
    }

    public function create_ajax($id)
    {
        // Mengambil satu kegiatan berdasarkan ID
        $kegiatan = DB::table('t_kegiatan as k')
            ->join('t_dosen_kegiatan as dk', 'k.kegiatan_id', '=', 'dk.kegiatan_id')
            ->join('m_kategori as kt', 'k.kategori_id', '=', 'kt.kategori_id')
            ->join('m_dosen as d', 'dk.dosen_id', '=', 'd.dosen_id')
            ->select('k.*', 'dk.*', 'kt.kategori_nama', 'd.*')
            ->where('k.kegiatan_id', $id)
            ->first(); // Menggunakan first() untuk mendapatkan satu record

        // Mengambil dosen yang terkait dengan kegiatan
        $dosen = DB::table('m_dosen as d')
            ->join('t_dosen_kegiatan as dk', 'dk.dosen_id', '=', 'd.dosen_id')
            ->join('t_kegiatan as k', 'k.kegiatan_id', '=', 'dk.kegiatan_id')
            ->select('d.*', 'dk.*') // Ubah select
            ->where('dk.kegiatan_id', $id)
            ->get();

        return view('kegiatan_dosen.create_agenda_ajax', [
            'kegiatan' => $kegiatan, // Sekarang ini adalah objek bukan koleksi
            'dosen' => $dosen,
        ]);
    }

    public function store_ajax(Request $request)
    {
        if ($request->ajax() || $request->wantsJson()) {
            // Aturan validasi
            $rules = [
                'kegiatan_id' => 'required|exists:t_kegiatan,kegiatan_id',
                'nama' => 'required|string|min:3|max:255',
                'status' => 'required|in:Belum,Berjalan,Selesai',
                'tanggal_mulai' => 'required|date',
                'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
                'dosen' => 'required|array|min:1',
                'dosen.*' => 'exists:m_dosen,dosen_id',
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
                    'kegiatan_id' => $request->kegiatan_id,
                    'status' => $request->status,
                ]);

                // Simpan dosen dan bobot
                foreach ($request->dosen as $index => $dosen_id) {
                    AgendaDosenModel::create([
                        'agenda_id' => $agenda->agenda_id,
                        'dosen_id' => $dosen_id,
                    ]);
                }

                DB::commit();

                return response()->json([
                    'status' => true,
                    'message' => 'Data agenda berhasil disimpan'
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

        return view('kegiatan_dosen.show_agenda_ajax', [
            'agenda' => $agenda,
            'dosenList' => $dosen,
        ]);
    }

    public function edit_ajax($id)
    {
        $agenda = DB::table('t_agenda as a')
            ->join('t_kegiatan_agenda as ka', 'a.agenda_id', '=', 'ka.agenda_id')
            ->join('t_kegiatan as k', 'ka.kegiatan_id', '=', 'k.kegiatan_id')
            ->select('a.*', 'ka.status', 'k.kegiatan_id', 'k.kegiatan_nama')
            ->where('a.agenda_id', $id)
            ->first();

        $dosen = DB::table('m_dosen as d')
            ->join('t_agenda_dosen as ad', 'd.dosen_id', '=', 'ad.dosen_id')
            ->join('t_kegiatan_agenda as ka', 'ad.agenda_id', '=', 'ka.agenda_id')
            ->join('t_kegiatan as k', 'ka.kegiatan_id', '=', 'k.kegiatan_id')
            ->join('t_agenda as a', 'a.agenda_id', '=', 'ka.agenda_id')
            ->select('d.*')
            ->where('a.agenda_id', $id)
            ->get();

        $agenda->dosen = $dosen; // Set the dosen property on the $agenda object

        return view('kegiatan_dosen.edit_agenda_ajax', [
            'agenda' => $agenda,
            'dosenList' => $dosen,
        ]);
    }

    public function update_ajax(Request $request, $id)
    {
        if ($request->ajax() || $request->wantsJson()) {
            // Aturan validasi
            $rules = [
                'kegiatan_id' => 'required|exists:t_kegiatan,kegiatan_id',
                'nama' => 'required|string|min:3|max:255',
                'status' => 'required|in:Belum,Berjalan,Selesai',
                'tanggal_mulai' => 'required|date',
                'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
                'dosen' => 'required|array|min:1',
                'dosen.*' => 'exists:m_dosen,dosen_id',
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
                // Temukan agenda yang akan diupdate
                $agenda = AgendaModel::findOrFail($id);

                // Update data agenda
                $agenda->update([
                    'nama' => $request->nama,
                    'tanggal_mulai' => $request->tanggal_mulai,
                    'tanggal_selesai' => $request->tanggal_selesai,
                ]);

                // Update status di tabel kegiatan agenda
                KegiatanAgendaModel::where('agenda_id', $id)->delete();
                KegiatanAgendaModel::Create(
                    [
                        'agenda_id' => $id,
                        'kegiatan_id' => $request->kegiatan_id,
                        'status' => $request->status,
                    ]
                );

                // Hapus dosen yang sudah ada
                AgendaDosenModel::where('agenda_id', $id)->delete();

                // Simpan dosen baru
                foreach ($request->dosen as $dosen_id) {
                    AgendaDosenModel::create([
                        'agenda_id' => $id,
                        'dosen_id' => $dosen_id,
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

    public function confirm_ajax($id)
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
            ->select('d.*')
            ->where('a.agenda_id', $id)
            ->get();

        $agenda->dosen = $dosen; // Set the dosen property on the $agenda object

        return view('kegiatan_dosen.confirm_agenda_ajax', [
            'agenda' => $agenda,
            'dosenList' => $dosen,
        ]);
    }

    public function delete_ajax(Request $request, $id)
    {
        if ($request->ajax() || $request->wantsJson()) {
            DB::beginTransaction();

            try {
                // Temukan agenda berdasarkan ID
                $agenda = AgendaModel::findOrFail($id);

                // Hapus entri dari tabel t_kegiatan_agenda yang terkait dengan agenda ini
                KegiatanAgendaModel::where('agenda_id', $agenda->agenda_id)->delete();

                // Hapus dosen yang terkait dengan agenda
                AgendaDosenModel::where('agenda_id', $agenda->agenda_id)->delete();

                // Hapus agenda itu sendiri
                AgendaModel::where('agenda_id', $agenda->agenda_id)->delete();

                DB::commit();
                return response()->json([
                    'status' => true,
                    'message' => 'Data agenda berhasil dihapus'
                ]);
            } catch (\Exception $e) {
                DB::rollback();
                Log::error('Error in delete_ajax: ' . $e->getMessage());

                return response()->json([
                    'status' => false,
                    'message' => 'Gagal menghapus data: ' . $e->getMessage()
                ], 500);
            }
        }
        return redirect('/');
    }
}
