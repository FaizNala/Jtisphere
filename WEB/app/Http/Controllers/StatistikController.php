<?php

namespace App\Http\Controllers;

use App\Models\DosenKegiatanModel;
use App\Models\KegiatanModel;
use App\Models\LevelModel;
use App\Models\PeranModel;
use App\Models\UserModel;
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
        return view('statistik.index', [
            'activeMenu' => $activeMenu,
            'breadcrumb' => $breadcrumb,
            'level' => $level
        ]);
    }

    public function list(Request $request)
    {
        // Menggunakan with untuk memuat relasi
        $users = UserModel::with(['dosen.dosenLevel.level', 'dosen.dosenKegiatan']) // Memuat relasi dosen, level, dan dosenKegiatan
            ->get(); // Mengambil semua data

        // Filter berdasarkan level
        $level_ids = $request->input('filter_level');
        if (!empty($level_ids)) {
            if (!is_array($level_ids)) {
                $level_ids = [$level_ids];
            }
            $users = $users->filter(function ($user) use ($level_ids) {
                // Memeriksa apakah user memiliki level yang sesuai
                return collect($user->dosen->dosenLevel)->pluck('level_id')->intersect($level_ids)->isNotEmpty();
            });
        }

        return DataTables::of($users)
            ->addIndexColumn()
            ->addColumn('nama', function ($user) {
                return $user->dosen->nama ?? ''; // Menampilkan nama dosen
            })
            ->addColumn('level_nama', function ($user) {
                return $user->dosen->dosenLevel->pluck('level.level_nama')->implode(', '); // Menggabungkan nama level
            })
            ->addColumn('total_kegiatan', function ($user) {
                return $user->dosen->dosenKegiatan->count(); // Menghitung total kegiatan dari dosenKegiatan
            })
            ->addColumn('total_bobot', function ($user) {
                return $user->dosen->dosenKegiatan->sum('bobot'); // Menghitung total bobot dari dosenKegiatan
            })
            ->addColumn('aksi', function ($user) {
                $btn = '<button onclick="modalAction(\'' . url('/statistik/' . $user->user_id . '/show_ajax') . '\')" class="btn btn-info btn-sm">Detail</button> ';
                return $btn;
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
        // Ambil data pengguna dan informasi terkait
        $users = UserModel::select(
            'm_user.user_id',
            'm_user.username',
            'm_dosen.nama',
            'm_dosen.nip',
            DB::raw('GROUP_CONCAT(DISTINCT m_level.level_id) as level_ids'),
            DB::raw('GROUP_CONCAT(DISTINCT m_level.level_nama SEPARATOR ", ") as level_nama'),
            DB::raw('COUNT(DISTINCT t_dosen_kegiatan.kegiatan_id) as total_kegiatan'),
            DB::raw('COALESCE(SUM(DISTINCT t_dosen_kegiatan.bobot), 0) as total_bobot')
        )
            ->join('m_dosen', 'm_user.user_id', '=', 'm_dosen.user_id')
            ->leftJoin('t_dosen_level', 'm_dosen.dosen_id', '=', 't_dosen_level.dosen_id')
            ->leftJoin('m_level', 't_dosen_level.level_id', '=', 'm_level.level_id')
            ->leftJoin('t_dosen_kegiatan', 't_dosen_kegiatan.dosen_id', '=', 'm_dosen.dosen_id')
            ->leftJoin('t_kegiatan', 't_kegiatan.kegiatan_id', '=', 't_dosen_kegiatan.kegiatan_id')
            ->groupBy(
                'm_user.user_id',
                'm_user.username',
                'm_dosen.nama',
                'm_dosen.nip'
            )
            ->get();

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
        $user = UserModel::select(
            'm_user.user_id',
            'm_user.username',
            'm_dosen.nama',
            'm_dosen.nip',
            DB::raw('GROUP_CONCAT(DISTINCT m_level.level_id) as level_ids'),
            DB::raw('GROUP_CONCAT(DISTINCT m_level.level_nama SEPARATOR ", ") as level_nama'),
            DB::raw('COUNT(DISTINCT t_dosen_kegiatan.kegiatan_id) as total_kegiatan'),
            DB::raw('COALESCE(SUM(DISTINCT t_dosen_kegiatan.bobot), 0) as total_bobot')
        )
            ->join('m_dosen', 'm_user.user_id', '=', 'm_dosen.user_id')
            ->leftJoin('t_dosen_level', 'm_dosen.dosen_id', '=', 't_dosen_level.dosen_id')
            ->leftJoin('m_level', 't_dosen_level.level_id', '=', 'm_level.level_id')
            ->leftJoin('t_dosen_kegiatan', 't_dosen_kegiatan.dosen_id', '=', 'm_dosen.dosen_id')
            ->leftJoin('t_kegiatan', 't_kegiatan.kegiatan_id', '=', 't_dosen_kegiatan.kegiatan_id')
            ->groupBy(
                'm_user.user_id',
                'm_user.username',
                'm_dosen.nama',
                'm_dosen.nip'
            )
            ->get();

        $pdf = Pdf::loadView('statistik.export_pdf', ['user' => $user]);
        $pdf->setPaper('a4', 'potrait');
        $pdf->setOption('isRemoteEnabled', true);
        $pdf->render();

        return $pdf->stream('Data Statistik Dosen ' . date('Y-m-d H:i:s') . '.pdf');
    }

    public function export_statistik($id)
    {
        $user = UserModel::select(
            'm_user.user_id',
            'm_user.username',
            'm_dosen.nama',
            'm_dosen.nip',
            DB::raw('GROUP_CONCAT(DISTINCT m_level.level_id) as level_ids'),
            DB::raw('GROUP_CONCAT(DISTINCT m_level.level_nama SEPARATOR ", ") as level_nama'),
            DB::raw('COUNT(DISTINCT t_dosen_kegiatan.kegiatan_id) as total_kegiatan'),
            DB::raw('COALESCE(SUM(DISTINCT t_dosen_kegiatan.bobot), 0) as total_bobot')
        )
            ->join('m_dosen', 'm_user.user_id', '=', 'm_dosen.user_id')
            ->leftJoin('t_dosen_level', 'm_dosen.dosen_id', '=', 't_dosen_level.dosen_id')
            ->leftJoin('m_level', 't_dosen_level.level_id', '=', 'm_level.level_id')
            ->leftJoin('t_dosen_kegiatan', 't_dosen_kegiatan.dosen_id', '=', 'm_dosen.dosen_id')
            ->leftJoin('t_kegiatan', 't_kegiatan.kegiatan_id', '=', 't_dosen_kegiatan.kegiatan_id')
            ->where('m_user.user_id', $id)
            ->groupBy(
                'm_user.user_id',
                'm_user.username',
                'm_dosen.nama',
                'm_dosen.nip'
            )
            ->first(); // Ubah menjadi first() untuk mendapatkan satu baris

        if (!$user) {
            abort(404, 'User not found');
        }

        $kegiatan = dosenKegiatanModel::select(
            't_dosen_kegiatan.bobot',
            't_kegiatan.kegiatan_nama',
            'm_peran.peran_nama',
        )
            ->join('t_kegiatan', 't_kegiatan.kegiatan_id', '=', 't_dosen_kegiatan.kegiatan_id')
            ->join('m_peran', 'm_peran.peran_id', '=', 't_dosen_kegiatan.peran_id')
            ->where('t_dosen_kegiatan.dosen_id', $id)
            ->get();

        $pdf = Pdf::loadView('statistik.export_statistik', ['user' => $user, 'kegiatan' => $kegiatan]);
        $pdf->setPaper('a4', 'portrait'); // Perbaiki typo 'potrait' menjadi 'portrait'
        $pdf->setOption('isRemoteEnabled', true);
        $pdf->render();

        return $pdf->stream('Data Statistik '. $user->nama . date(' Y-m-d H:i:s') . '.pdf');
    }

}
