<?php
// namespace App\Http\Controllers;
// use Illuminate\Http\Request;

// class HomeController extends Controller{
//     public function index(){
//         return view('home');
//     }
// }
namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Menampilkan halaman home.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {


        // Mengirim data produk ke view
        return view('home.index');
    }
}

?>
