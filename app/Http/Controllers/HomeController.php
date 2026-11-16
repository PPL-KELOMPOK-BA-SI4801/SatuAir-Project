<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

/**
 * Controller untuk halaman utama (landing page).
 */
class HomeController extends Controller
{
    /**
     * Tampilkan halaman utama aplikasi.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        return view('home');
    }
}
