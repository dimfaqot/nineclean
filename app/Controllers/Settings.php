<?php

namespace App\Controllers;

class Settings extends BaseController
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
        $data = db(menu()['tabel'])->orderBy("nama", "ASC")->get()->getResultArray();
        return view(menu()['controller'] . '/' . menu()['controller'] . '_' . 'landing', ['judul' => menu()['menu'], "data" => $data]);
    }
    public function add()
    {
        $input = [
            'nama'       => strtolower(clear($this->request->getVar('nama'))),
            'value'       => clear($this->request->getVar('value'))
        ];


        // Cek duplikat
        if (db(menu()['tabel'])->where('nama', $input['nama'])->countAllResults() > 0) {
            gagal(base_url(menu()['controller']), 'Setting existed');
        }


        // Simpan data  
        db(menu()['tabel'])->insert($input)
            ? sukses(base_url(menu()['controller']), 'Sukses')
            : gagal(base_url(menu()['controller']), 'Gagal');
    }

    public function edit()
    {
        $id = clear($this->request->getVar('id'));

        $q = db(menu()['tabel'])->where('id', $id)->get()->getRowArray();

        if (!$q) {
            gagal(base_url(menu()['controller']), "Id not found");
        }

        $q = [
            'nama'       => strtolower(clear($this->request->getVar('nama'))),
            'value'       => clear($this->request->getVar('value'))
        ];

        if ((db(menu()['tabel'])->whereNotIn('id', [$id]))->where("nama", $q['nama'])->get()->getRowArray()) {
            gagal(base_url(menu()['controller']), "Setting existed");
        }

        // Simpan data
        db(menu()['tabel'])->where('id', $id)->update($q)
            ? sukses(base_url(menu()['controller']), 'Sukses')
            : gagal(base_url(menu()['controller']), 'Gagal');
    }
}
