<?php

namespace App\Controllers;

class Home extends BaseController
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
        return view(menu()['controller'] . '/' . menu()['controller'] . '_' . 'landing', ['judul' => menu()['menu']]);
    }

    public function delete()
    {
        $id = clear($this->request->getVar('id'));
        $tabel = clear($this->request->getVar('tabel'));
        $q = db($tabel)->where('id', $id)->get()->getRowArray();

        if (!$q) {
            gagal_js("Id not found");
        }

        (db($tabel)->where('id', $id)->delete()) ? sukses_js("Sukses") : gagal_js("Gagal");
    }
}
