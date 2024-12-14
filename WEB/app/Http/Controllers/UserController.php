<?php

namespace App\Http\Controllers;

use App\Models\LevelModel;
use App\Models\UserModel;
use App\Models\DosenModel;
use App\Models\DosenLevelModel;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Providers\Auth\Illuminate;
use Yajra\DataTables\Facades\DataTables;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    public function index()
    {
        $activeMenu = 'user';
        $breadcrumb = (object) [
            'title' => 'Daftar User',
            'list' => ['Home', 'User']
        ];

        $level = LevelModel::select('level_id', 'level_nama')->get();
        return view('user.index', [
            'activeMenu' => $activeMenu,
            'breadcrumb' => $breadcrumb,
            'level' => $level
        ]);
    }

    public function list(Request $request)
    {
        // Menggunakan with untuk memuat relasi
        $users = UserModel::with(['dosen.dosenLevel.level']) // Memuat relasi dosen dan level
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
            ->addColumn('aksi', function ($user) {
                $btn = '<button onclick="modalAction(\'' . url('/user/' . $user->user_id . '/show_ajax') . '\')" class="btn btn-info btn-sm">Detail</button> ';
                $userRole = optional(optional(auth()->user()->dosen)->dosenLevel)->first()->level->level_kode ?? null;
                if ($userRole == 'ADM') {
                    $btn .= '<button onclick="modalAction(\'' . url('/user/' . $user->user_id . '/edit_ajax') . '\')" class="btn btn-warning btn-sm">Edit</button> ';
                    $btn .= '<button onclick="modalAction(\'' . url('/user/' . $user->user_id . '/delete_ajax') . '\')" class="btn btn-danger btn-sm">Hapus</button> ';
                }
                return $btn;
            })
            ->rawColumns(['aksi'])
            ->make(true);
    }

    public function create_ajax()
    {
        $level = LevelModel::select('level_id', 'level_nama')->get();

        return view('user.create_ajax')
            ->with('level', $level);
    }

    public function store_ajax(Request $request)
    {
        if ($request->ajax() || $request->wantsJson()) {
            $rules = [
                'username' => 'required|string|min:3|max:20|unique:m_user,username',
                'password' => 'required|string|min:5|max:20',
                'nama' => 'required|string|min:3|max:50',
                'nip' => 'required|digits_between:15,25|unique:m_dosen,nip',
                'level' => 'required|array|min:1',
                'level.*' => 'exists:m_level,level_id'
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
                $user = UserModel::create([
                    'username' => $request->username,
                    'password' => Hash::make($request->password),
                ]);

                $dosen = DosenModel::create([
                    'user_id' => $user->user_id,
                    'nama' => $request->nama,
                    'nip' => $request->nip,
                ]);

                foreach ($request->level as $level_id) {
                    DosenLevelModel::create([
                        'dosen_id' => $dosen->dosen_id,
                        'level_id' => $level_id,
                    ]);
                }

                DB::commit();
                return response()->json([
                    'status' => true,
                    'message' => 'Data user berhasil disimpan'
                ]);
            } catch (\Exception $e) {
                DB::rollback();
                return response()->json([
                    'status' => false,
                    'message' => 'Gagal menyimpan data'
                ], 500);
            }
        }
        return redirect('/');
    }

    public function show_ajax($id)
    {
        $user = UserModel::select(
            'm_user.user_id',
            'm_user.username',
            'm_dosen.nama',
            'm_dosen.nip',
            DB::raw('GROUP_CONCAT(m_level.level_id) as level_ids'),
            DB::raw('GROUP_CONCAT(m_level.level_nama SEPARATOR ", ") as level_nama')
        )
            ->join('m_dosen', 'm_user.user_id', '=', 'm_dosen.user_id')
            ->join('t_dosen_level', 'm_dosen.dosen_id', '=', 't_dosen_level.dosen_id')
            ->join('m_level', 't_dosen_level.level_id', '=', 'm_level.level_id')
            ->where('m_user.user_id', $id)
            ->groupBy('m_user.user_id', 'm_user.username', 'm_dosen.nama', 'm_dosen.nip')
            ->first();

        if (!$user) {
            return response()->json(['message' => 'User tidak ditemukan'], 404);
        }

        return view('user.show_ajax', compact('user'));
    }

    public function edit_ajax($id)
    {
        $user = UserModel::select('m_user.user_id', 'm_user.username', 'm_dosen.nama', 'm_dosen.nip', 'm_dosen.dosen_id')
            ->join('m_dosen', 'm_user.user_id', '=', 'm_dosen.user_id')
            ->where('m_user.user_id', $id)
            ->first();

        if (!$user) {
            return response()->json(['message' => 'User tidak ditemukan'], 404);
        }

        $userLevels = DosenLevelModel::where('dosen_id', $user->dosen_id)->pluck('level_id')->toArray();
        $allLevels = LevelModel::select('level_id', 'level_nama')->get();

        return view('user.edit_ajax', compact('user', 'userLevels', 'allLevels'));
    }

    public function update_ajax(Request $request, $id)
    {
        if ($request->ajax() || $request->wantsJson()) {
            $rules = [
                'username' => 'required|string|min:3|max:20|unique:m_user,username,' . $id . ',user_id',
                'nama' => 'required|string|min:3|max:50',
                'nip' => 'required|digits_between:15,25|unique:m_dosen,nip,' . $id . ',user_id',
                'level' => 'required|array|min:1',
                'level.*' => 'exists:m_level,level_id'
            ];

            if ($request->filled('password')) {
                $rules['password'] = 'string|min:5|max:20';
            }

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
                $user = UserModel::findOrFail($id);
                $user->username = $request->username;
                if ($request->filled('password')) {
                    $user->password = Hash::make($request->password);
                }
                $user->save();

                $dosen = DosenModel::where('user_id', $id)->firstOrFail();
                $dosen->nama = $request->nama;
                $dosen->nip = $request->nip;
                $dosen->save();

                DosenLevelModel::where('dosen_id', $dosen->dosen_id)->delete();

                foreach ($request->level as $level_id) {
                    DosenLevelModel::create([
                        'dosen_id' => $dosen->dosen_id,
                        'level_id' => $level_id,
                    ]);
                }

                DB::commit();
                return response()->json([
                    'status' => true,
                    'message' => 'Data user berhasil diperbarui'
                ]);
            } catch (\Exception $e) {
                DB::rollback();
                return response()->json([
                    'status' => false,
                    'message' => 'Gagal memperbarui data'
                ], 500);
            }
        }
        return redirect('/');
    }

    public function confirm_ajax($id)
    {
        $user = UserModel::select(
            'm_user.user_id',
            'm_user.username',
            'm_dosen.nama',
            'm_dosen.nip',
            DB::raw('GROUP_CONCAT(m_level.level_nama SEPARATOR ", ") as level_nama')
        )
            ->join('m_dosen', 'm_user.user_id', '=', 'm_dosen.user_id')
            ->join('t_dosen_level', 'm_dosen.dosen_id', '=', 't_dosen_level.dosen_id')
            ->join('m_level', 't_dosen_level.level_id', '=', 'm_level.level_id')
            ->where('m_user.user_id', $id)
            ->groupBy('m_user.user_id', 'm_user.username', 'm_dosen.nama', 'm_dosen.nip')
            ->first();

        if (!$user) {
            return response()->json(['message' => 'User tidak ditemukan'], 404);
        }

        return view('user.confirm_ajax', compact('user'));
    }

    public function delete_ajax(Request $request, $id)
    {
        if ($request->ajax() || $request->wantsJson()) {
            DB::beginTransaction();

            try {
                $user = UserModel::findOrFail($id);
                $dosen = DosenModel::where('user_id', $id)->firstOrFail();

                DosenLevelModel::where('dosen_id', $dosen->dosen_id)->delete();
                $dosen->delete();
                $user->delete();

                DB::commit();
                return response()->json([
                    'status' => true,
                    'message' => 'Data user berhasil dihapus'
                ]);
            } catch (\Exception $e) {
                DB::rollback();
                return response()->json([
                    'status' => false,
                    'message' => 'Gagal menghapus data'
                ], 500);
            }
        }
        return redirect('/');
    }

    public function import()
    {
        return view('user.import');
    }

    public function import_ajax(Request $request)
    {
        if ($request->ajax() || $request->wantsJson()) {
            $rules = [
                'file_user' => ['required', 'mimes:xlsx', 'max:1024']
            ];

            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validasi Gagal',
                    'msgField' => $validator->errors()
                ]);
            }

            $file = $request->file('file_user');
            $reader = IOFactory::createReader('Xlsx');
            $reader->setReadDataOnly(true);
            $spreadsheet = $reader->load($file->getRealPath());
            $sheet = $spreadsheet->getActiveSheet();
            $data = $sheet->toArray(null, false, true, true);

            DB::beginTransaction();
            try {
                if (count($data) > 1) {
                    foreach ($data as $baris => $value) {
                        if ($baris > 1) {
                            // Cari atau buat user
                            $user = UserModel::firstOrCreate(
                                ['username' => $value['A']],
                                [
                                    'password' => bcrypt($value['B']),
                                    'created_at' => now(),
                                    'updated_at' => now()
                                ]
                            );

                            // Cari atau buat dosen
                            $dosen = DosenModel::firstOrCreate(
                                [
                                    'nama' => $value['C'],
                                    'nip' => $value['D']
                                ],
                                [
                                    'user_id' => $user->user_id,
                                    'created_at' => now(),
                                    'updated_at' => now()
                                ]
                            );

                            // Proses level dosen dengan mencegah duplikasi
                            $levels = explode(',', $value['E'] ?? '');
                            foreach ($levels as $level) {
                                $trimmedLevel = trim($level);

                                // Cek apakah kombinasi dosen_id dan level_id sudah ada
                                DosenLevelModel::firstOrCreate([
                                    'dosen_id' => $dosen->dosen_id,
                                    'level_id' => $trimmedLevel
                                ]);
                            }
                        }
                    }

                    DB::commit();
                    return response()->json([
                        'status' => true,
                        'message' => 'Data user berhasil diimport'
                    ]);
                }

                return response()->json([
                    'status' => false,
                    'message' => 'Tidak ada data yang diimport'
                ]);
            } catch (\Exception $e) {
                DB::rollback();
                return response()->json([
                    'status' => false,
                    'message' => 'Gagal mengimport data: ' . $e->getMessage(),
                    'error' => $e->getTraceAsString()
                ], 500);
            }
        }
        return redirect('/');
    }

    public function export_excel()
    {
        $users = UserModel::select(
            'm_user.user_id',
            'm_user.username',
            'm_dosen.nama',
            'm_dosen.nip',
            DB::raw('GROUP_CONCAT(m_level.level_nama SEPARATOR ", ") as level_nama')
        )
            ->join('m_dosen', 'm_user.user_id', '=', 'm_dosen.user_id')
            ->join('t_dosen_level', 'm_dosen.dosen_id', '=', 't_dosen_level.dosen_id')
            ->join('m_level', 't_dosen_level.level_id', '=', 'm_level.level_id')
            ->groupBy('m_user.user_id', 'm_user.username', 'm_dosen.nama', 'm_dosen.nip')
            ->get();

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set headers
        $sheet->setCellValue('A1', 'No');
        $sheet->setCellValue('B1', 'Username');
        $sheet->setCellValue('C1', 'Nama');
        $sheet->setCellValue('D1', 'NIP');
        $sheet->setCellValue('E1', 'Level');

        // Make headers bold
        $sheet->getStyle('A1:E1')->getFont()->setBold(true);

        // Populate data
        $row = 2;
        foreach ($users as $index => $user) {
            $sheet->setCellValue('A' . $row, $index + 1);
            $sheet->setCellValue('B' . $row, $user->username);
            $sheet->setCellValue('C' . $row, $user->nama);
            $sheet->setCellValue('D' . $row, $user->nip);
            $sheet->setCellValue('E' . $row, $user->level_nama);
            $row++;
        }

        // Auto-size columns
        foreach (range('A', 'E') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
        $filename = 'Data User ' . date('Y-m-d_His') . '.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer->save('php://output');
        exit;
    }

    public function export_pdf()
    {
        $users = UserModel::select(
            'm_user.user_id',
            'm_user.username',
            'm_dosen.nama',
            'm_dosen.nip',
            DB::raw('GROUP_CONCAT(m_level.level_nama SEPARATOR ", ") as level_nama')
        )
            ->join('m_dosen', 'm_user.user_id', '=', 'm_dosen.user_id')
            ->join('t_dosen_level', 'm_dosen.dosen_id', '=', 't_dosen_level.dosen_id')
            ->join('m_level', 't_dosen_level.level_id', '=', 'm_level.level_id')
            ->groupBy('m_user.user_id', 'm_user.username', 'm_dosen.nama', 'm_dosen.nip')
            ->get();

        $pdf = PDF::loadView('user.export_pdf', [
            'users' => $users,
            'title' => 'Data User'
        ]);

        $pdf->setPaper('A4', 'portrait');
        $pdf->setOption('isRemoteEnabled', true);
        $pdf->render();

        return $pdf->stream('Data_User_' . date('Y-m-d_His') . '.pdf');
    }
    public function switchRole($level_id)
    {
        // Check if the user has the requested role
        $user = Auth::user();
        $hasRole = $user->dosen->dosenLevel->contains('level_id', $level_id);

        if ($hasRole) {
            // Set the current role in the session
            session(['current_level_id' => $level_id]);

            return redirect('/')->with('success', 'Role switched successfully!');
        }

        return redirect('/')->with('error', 'Unauthorized role switch!');
    }
}
