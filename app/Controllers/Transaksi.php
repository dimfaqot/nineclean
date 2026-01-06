<?php

namespace App\Controllers;

class Transaksi extends BaseController
{
    function __construct()
    {
        if (!session('id')) {
            session()->setFlashdata('gagal', "Ligin first");
            header("Location: " . base_url());
            die;
        }
    }
    public function index(): string
    {

        if (session('db') == "playground" || session('db') == "playbox") {
            return view('playground/transaksi_landing', ['judul' => menu()['menu']]);
        } else {
            return view(menu()['controller'] . '/' . menu()['controller'] . "_" . 'landing', ['judul' => menu()['menu']]);
        }
    }
}
