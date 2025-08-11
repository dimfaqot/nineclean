<?php

namespace App\Controllers;

class Profile extends BaseController
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

        return view(menu()['controller'] . '/' . menu()['controller'] . "_" . 'landing', ['judul' => menu()['menu'], "data" => profile()]);
    }


    public function edit()
    {
        $id = clear($this->request->getVar('id'));

        $q = db(menu()['tabel'])->where('id', $id)->get()->getRowArray();

        if (!$q) {
            gagal(base_url(menu()['controller']), "Id not found");
        }
        $q = [
            'nama'       => upper_first(clear($this->request->getVar('nama'))),
            'manager'       => upper_first(clear($this->request->getVar('manager'))),
            'pendiri'       => upper_first(clear($this->request->getVar('pendiri'))),
            'tgl_berdiri'       => strtotime($this->request->getVar('tgl_berdiri')),
            'sub_unit'       => upper_first(clear($this->request->getVar('sub_unit'))),
            'modal_asal'       => upper_first(clear($this->request->getVar('modal_asal')))
        ];

        // Simpan data
        db(menu()['tabel'])->where('id', $id)->update($q)
            ? sukses(base_url(menu()['controller']), 'Sukses')
            : gagal(base_url(menu()['controller']), 'Gagal');
    }
}
