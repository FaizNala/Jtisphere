<?php

namespace App\Http\Controllers;

use App\Models\DosenKegiatanModel;
use App\Models\KegiatanModel;
use App\Models\LevelModel;
use App\Models\PeranModel;
use App\Models\UserModel;
use App\Models\PeriodeModel;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use Yajra\DataTables\Facades\DataTables;

class StatistikController extends Controller
{
    public function index()
    {
        $activeMenu = 'statistik';
        $breadcrumb = (object) [
            'title' => 'Statistik Dosen',
            'list' => ['Home', 'Statistik Dosen']
        ];

        $level = LevelModel::select('level_id', 'level_nama')->get();
        $periode = PeriodeModel::orderBy('tanggal_mulai', 'DESC')->get();
        return view('statistik.index', [
            'activeMenu' => $activeMenu,
            'breadcrumb' => $breadcrumb,
            'level' => $level,
            'periode' => $periode
        ]);
    }

    public function list(Request $request)
{
    // Query dasar dengan eager loading
    $query = UserModel::with([
        'dosen.dosenLevel.level',
        'dosen.dosenKegiatan.kegiatan'
    ]);

    // Filter berdasarkan level
    $level_id = $request->input('filter_level');
    if (!empty($level_id)) {
        $query->whereHas('dosen.dosenLevel', function ($q) use ($level_id) {
            $q->where('level_id', $level_id);
        });
    }

    // Filter berdasarkan periode
    $periode_id = $request->input('filter_periode');
    session(['periode' => $periode_id]);

    // Ambil data
    $users = $query->get();

    return DataTables::of($users)
        ->addIndexColumn()
        ->addColumn('nama', function ($user) {
            return optional($user->dosen)->nama ?? '';
        })
        ->addColumn('level_nama', function ($user) {
            return $user->dosen->dosenLevel->pluck('level.level_nama')->implode(', ');
        })
        ->addColumn('total_kegiatan', function ($user) use ($periode_id) {
            // Jika periode tidak dipilih, hitung semua kegiatan
            if (empty($periode_id)) {
                return $user->dosen->dosenKegiatan->count();
            }

            // Filter kegiatan berdasarkan periode
            $totalKegiatan = $user->dosen->dosenKegiatan()
                ->whereHas('kegiatan', function ($query) use ($periode_id) {
                    $query->where('periode_id', $periode_id);
                })
                ->count();

            return $totalKegiatan ?? 0;
        })
        ->addColumn('total_bobot', function ($user) use ($periode_id) {
            // Jika periode tidak dipilih, hitung semua bobot
            if (empty($periode_id)) {
                return $user->dosen->dosenKegiatan->sum('bobot');
            }

            // Filter bobot berdasarkan periode
            $totalBobot = $user->dosen->dosenKegiatan()
                ->whereHas('kegiatan', function ($query) use ($periode_id) {
                    $query->where('periode_id', $periode_id);
                })
                ->sum('bobot');

            return $totalBobot ?? 0;
        })
        ->addColumn('aksi', function ($user) {
            return '<button onclick="modalAction(\'' . url('/statistik/' . $user->user_id . '/show_ajax') . '\')" class="btn btn-info btn-sm">Detail</button>';
        })
        ->rawColumns(['aksi'])
        ->make(true);
}

    public function show_ajax($id)
    {
        $user = UserModel::select(
            'm_user.user_id',
            'm_user.username',
            'm_dosen.nama',
            'm_dosen.nip',
            'm_dosen.avatar',
            DB::raw('GROUP_CONCAT(m_level.level_id) as level_ids'),
            DB::raw('GROUP_CONCAT(m_level.level_nama SEPARATOR ", ") as level_nama')
        )
            ->join('m_dosen', 'm_user.user_id', '=', 'm_dosen.user_id')
            ->join('t_dosen_level', 'm_dosen.dosen_id', '=', 't_dosen_level.dosen_id')
            ->join('m_level', 't_dosen_level.level_id', '=', 'm_level.level_id')
            ->where('m_user.user_id', $id)
            ->groupBy('m_user.user_id', 'm_user.username', 'm_dosen.nama', 'm_dosen.nip')
            ->first();

        $dosenKegiatan = DosenKegiatanModel::with('kegiatan')
            ->where('dosen_id', $id)
            ->whereHas('kegiatan', function ($query) {
                $query->where('periode_id', session('periode'));
            })
            ->get();

        $kegiatan = KegiatanModel::select('kegiatan_id', 'kegiatan_nama')->get();
        $peran = PeranModel::select('peran_id', 'peran_nama')->get();

        $total_bobot = $dosenKegiatan->where('dosen_id', $id)->sum('bobot');

        if (!$user) {
            return response()->json(['message' => 'User tidak ditemukan'], 404);
        }

        return view('statistik.show_ajax', compact(['user', 'dosenKegiatan', 'kegiatan', 'peran', 'total_bobot']));
    }


    public function export_excel()
    {
        $periode_id = session('periode');
        if (empty($periode_id)) {
            return redirect()->back()->with('error', 'Pilih periode terlebih dahulu');
        }

        $users = UserModel::select(
            'm_user.user_id',
            'm_user.username',
            'm_dosen.nama',
            'm_dosen.nip',
            DB::raw('GROUP_CONCAT(DISTINCT m_level.level_id) as level_ids'),
            DB::raw('GROUP_CONCAT(DISTINCT m_level.level_nama SEPARATOR ", ") as level_nama')
        )
        ->leftJoin('m_dosen', 'm_user.user_id', '=', 'm_dosen.user_id')
        ->leftJoin('t_dosen_level', 'm_dosen.dosen_id', '=', 't_dosen_level.dosen_id')
        ->leftJoin('m_level', 't_dosen_level.level_id', '=', 'm_level.level_id')
        ->leftJoin('t_dosen_kegiatan', 't_dosen_kegiatan.dosen_id', '=', 'm_dosen.dosen_id')
        ->leftJoin('t_kegiatan', function($join) use ($periode_id) {
            $join->on('t_kegiatan.kegiatan_id', '=', 't_dosen_kegiatan.kegiatan_id')
                 ->where('t_kegiatan.periode_id', $periode_id);
        })
        ->groupBy(
            'm_user.user_id',
            'm_user.username',
            'm_dosen.nama',
            'm_dosen.nip'
        )
        ->get()
        ->map(function($user) use ($periode_id) {
            // Hitung total kegiatan dan bobot secara manual
            $totalKegiatan = $user->dosen->dosenKegiatan()
                ->whereHas('kegiatan', function($query) use ($periode_id) {
                    $query->where('periode_id', $periode_id);
                })
                ->count();

            $totalBobot = $user->dosen->dosenKegiatan()
                ->whereHas('kegiatan', function($query) use ($periode_id) {
                    $query->where('periode_id', $periode_id);
                })
                ->sum('bobot');

            $user->total_kegiatan = $totalKegiatan;
            $user->total_bobot = $totalBobot;

            return $user;
        });

        // Load library excel
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set header columns
        $sheet->setCellValue('A1', 'No');
        $sheet->setCellValue('B1', 'Username');
        $sheet->setCellValue('C1', 'Nama Dosen');
        $sheet->setCellValue('D1', 'NIP');
        $sheet->setCellValue('E1', 'Level');
        $sheet->setCellValue('F1', 'Total Kegiatan');
        $sheet->setCellValue('G1', 'Total Bobot');

        $sheet->getStyle('A1:G1')->getFont()->setBold(true);

        $no = 1; // No data starts from 1
        $baris = 2; // Data rows start from row 2
        foreach ($users as $user) {
            $sheet->setCellValue('A' . $baris, $no);
            $sheet->setCellValue('B' . $baris, $user->username);
            $sheet->setCellValue('C' . $baris, $user->nama);
            $sheet->setCellValue('D' . $baris, $user->nip);
            $sheet->setCellValue('E' . $baris, $user->level_nama ?? 'Tidak ada level');
            $sheet->setCellValue('F' . $baris, $user->total_kegiatan);
            $sheet->setCellValue('G' . $baris, $user->total_bobot);
            $baris++;
            $no++;
        }

        // Auto size columns
        foreach (range('A', 'G') as $columnID) {
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
        }

        $sheet->setTitle('Data Statistik Dosen');
        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
        $filename = 'Data Statistik Dosen ' . date('Y-m-d H:i:s') . '.xlsx';

        // Output headers
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
        $periode_id = session('periode');
        if (empty($periode_id)) {
            return redirect()->back()->with('error', 'Pilih periode terlebih dahulu');
        }

        $users = UserModel::select(
            'm_user.user_id',
            'm_user.username',
            'm_dosen.nama',
            'm_dosen.nip',
            DB::raw('GROUP_CONCAT(DISTINCT m_level.level_id) as level_ids'),
            DB::raw('GROUP_CONCAT(DISTINCT m_level.level_nama SEPARATOR ", ") as level_nama')
        )
        ->leftJoin('m_dosen', 'm_user.user_id', '=', 'm_dosen.user_id')
        ->leftJoin('t_dosen_level', 'm_dosen.dosen_id', '=', 't_dosen_level.dosen_id')
        ->leftJoin('m_level', 't_dosen_level.level_id', '=', 'm_level.level_id')
        ->leftJoin('t_dosen_kegiatan', 't_dosen_kegiatan.dosen_id', '=', 'm_dosen.dosen_id')
        ->leftJoin('t_kegiatan', function($join) use ($periode_id) {
            $join->on('t_kegiatan.kegiatan_id', '=', 't_dosen_kegiatan.kegiatan_id')
                 ->where('t_kegiatan.periode_id', $periode_id);
        })
        ->groupBy(
            'm_user.user_id',
            'm_user.username',
            'm_dosen.nama',
            'm_dosen.nip'
        )
        ->get()
        ->map(function($user) use ($periode_id) {
            // Hitung total kegiatan dan bobot secara manual
            $totalKegiatan = $user->dosen->dosenKegiatan()
                ->whereHas('kegiatan', function($query) use ($periode_id) {
                    $query->where('periode_id', $periode_id);
                })
                ->count();

            $totalBobot = $user->dosen->dosenKegiatan()
                ->whereHas('kegiatan', function($query) use ($periode_id) {
                    $query->where('periode_id', $periode_id);
                })
                ->sum('bobot');

            $user->total_kegiatan = $totalKegiatan;
            $user->total_bobot = $totalBobot;

            return $user;
        });

        $pdf = Pdf::loadView('statistik.export_pdf', ['user' => $users]);
        $pdf->setPaper('a4', 'potrait');
        $pdf->setOption('isRemoteEnabled', true);
        $pdf->render();

        return $pdf->stream('Data Statistik Dosen ' . date('Y-m-d H:i:s') . '.pdf');
    }

    public function export_statistik($id)
    {
        $periode_id = session('periode');
        if (empty($periode_id)) {
            return redirect()->back()->with('error', 'Pilih periode terlebih dahulu');
        }

        $user = UserModel::select(
            'm_user.user_id',
            'm_user.username',
            'm_dosen.nama',
            'm_dosen.nip',
            DB::raw('GROUP_CONCAT(DISTINCT m_level.level_id) as level_ids'),
            DB::raw('GROUP_CONCAT(DISTINCT m_level.level_nama SEPARATOR ", ") as level_nama')
        )
            ->leftJoin('m_dosen', 'm_user.user_id', '=', 'm_dosen.user_id')
            ->leftJoin('t_dosen_level', 'm_dosen.dosen_id', '=', 't_dosen_level.dosen_id')
            ->leftJoin('m_level', 't_dosen_level.level_id', '=', 'm_level.level_id')
            ->leftJoin('t_dosen_kegiatan', 't_dosen_kegiatan.dosen_id', '=', 'm_dosen.dosen_id')
            ->leftJoin('t_kegiatan', function ($join) use ($periode_id) {
                $join->on('t_kegiatan.kegiatan_id', '=', 't_dosen_kegiatan.kegiatan_id')
                    ->where('t_kegiatan.periode_id', $periode_id);
            })
            ->where('m_user.user_id', $id)
            ->groupBy(
                'm_user.user_id',
                'm_user.username',
                'm_dosen.nama',
                'm_dosen.nip'
            )
            ->first();

        if (!$user) {
            abort(404, 'User not found');
        }

        // Hitung total kegiatan dan bobot secara manual
        $totalKegiatan = $user->dosen->dosenKegiatan()
            ->whereHas('kegiatan', function ($query) use ($periode_id) {
                $query->where('periode_id', $periode_id);
            })
            ->count();

        $totalBobot = $user->dosen->dosenKegiatan()
            ->whereHas('kegiatan', function ($query) use ($periode_id) {
                $query->where('periode_id', $periode_id);
            })
            ->sum('bobot');

        $user->total_kegiatan = $totalKegiatan;
        $user->total_bobot = $totalBobot;

        $kegiatan = DosenKegiatanModel::select(
            't_dosen_kegiatan.bobot',
            't_kegiatan.kegiatan_nama',
            'm_peran.peran_nama'
        )
            ->join('t_kegiatan', function ($join) use ($periode_id) {
                $join->on('t_kegiatan.kegiatan_id', '=', 't_dosen_kegiatan.kegiatan_id')
                    ->where('t_kegiatan.periode_id', $periode_id);
            })
            ->join('m_peran', 'm_peran.peran_id', '=', 't_dosen_kegiatan.peran_id')
            ->where('t_dosen_kegiatan.dosen_id', $user->dosen->dosen_id)
            ->get();

        $pdf = Pdf::loadView('statistik.export_statistik', [
            'user' => $user,
            'kegiatan' => $kegiatan,
            'periode_id' => $periode_id
        ]);

        $pdf->setPaper('a4', 'portrait');
        $pdf->setOption('isRemoteEnabled', true);

        return $pdf->stream('Data Statistik ' . $user->nama . date(' Y-m-d H:i:s') . '.pdf');
    }
}
