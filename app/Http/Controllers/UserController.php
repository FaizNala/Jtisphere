<?php

namespace App\Http\Controllers;

use App\Models\LevelModel;
use App\Models\UserModel;
use App\Models\DosenModel;
use App\Models\DosenLevelModel;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;

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
        $users = UserModel::select('m_user.user_id', 'm_user.username', 'm_dosen.nama', 'm_dosen.nip',
                DB::raw('GROUP_CONCAT(m_level.level_id) as level_ids'),
                DB::raw('GROUP_CONCAT(m_level.level_nama SEPARATOR ", ") as level_nama')
            )
            ->join('m_dosen', 'm_user.user_id', '=', 'm_dosen.user_id')
            ->join('t_dosen_level', 'm_dosen.dosen_id', '=', 't_dosen_level.dosen_id')
            ->join('m_level', 't_dosen_level.level_id', '=', 'm_level.level_id')
            ->groupBy('m_user.user_id', 'm_user.username', 'm_dosen.nama', 'm_dosen.nip');

        $level_ids = $request->input('filter_level');
        if (!empty($level_ids)) {
            if (!is_array($level_ids)) {
                $level_ids = [$level_ids];
            }
            $users->havingRaw('FIND_IN_SET(?, level_ids)', [$level_ids]);
        }

        return DataTables::of($users)
            ->addIndexColumn()
            ->addColumn('aksi', function ($user) {
                $btn  = '<button onclick="modalAction(\'' . url('/user/' . $user->user_id .
                    '/show_ajax') . '\')" class="btn btn-info btn-sm">Detail</button> ';
                $btn .= '<button onclick="modalAction(\'' . url('/user/' . $user->user_id .
                    '/edit_ajax') . '\')" class="btn btn-warning btn-sm">Edit</button> ';
                $btn .= '<button onclick="modalAction(\'' . url('/user/' . $user->user_id .
                    '/delete_ajax') . '\')"  class="btn btn-danger btn-sm">Hapus</button> ';
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
                'username' => 'required|string|min:3|unique:m_user,username',
                'password' => 'required|min:5',
                'nama' => 'required|string|max:100',
                'nip' => 'required|string|unique:m_dosen,nip',
                'level' => 'required|array|min:1',
                'level.*' => 'exists:m_level,level_id'
            ];

            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validasi Gagal',
                    'msgField' => $validator->errors(),
                ]);
            }

            DB::beginTransaction();

            try {
                $user = UserModel::create([
                    'username' => $request->username,
                    'password' => bcrypt($request->password),
                ]);

                DosenModel::create([
                    'user_id' => $user->user_id,
                    'nama' => $request->nama,
                    'nip' => $request->nip,
                ]);

                foreach ($request->level as $level_id) {
                    DosenLevelModel::create([
                        'user_id' => $user->user_id,
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
                    'message' => 'Terjadi kesalahan saat menyimpan data'
                ]);
            }
        }
        return redirect('/');
    }

    // Implementasikan method lain seperti show_ajax, edit_ajax, update_ajax, confirm_ajax, delete_ajax
    // sesuai dengan kebutuhan Anda, dengan menyesuaikan logika untuk multi-level

    public function import_ajax(Request $request)
    {
        // Implementasi import seperti yang sudah ada, dengan penyesuaian untuk multi-level
    }

    public function export_excel()
    {
        // Implementasi export excel seperti yang sudah ada, dengan penyesuaian untuk multi-level
    }

    public function export_pdf()
    {
        // Implementasi export PDF seperti yang sudah ada, dengan penyesuaian untuk multi-level
    }
}
