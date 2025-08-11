<?php

namespace App\Controllers;

class Options extends BaseController
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
        $data = db(menu()['tabel'])->orderBy("kategori", "ASC")->get()->getResultArray();
        return view(menu()['controller'] . '/' . menu()['controller'] . '_' . 'landing', ['judul' => menu()['menu'], "data" => $data]);
    }
    public function add()
    {
        $input = [
            'kategori'       => upper_first(clear($this->request->getVar('kategori'))),
            'value'       => upper_first(clear($this->request->getVar('value')))
        ];


        // Cek duplikat
        if (db(menu()['tabel'])->where('kategori', $input['kategori'])->where("value", $input['value'])->countAllResults() > 0) {
            gagal(base_url(menu()['controller']), 'Value existed');
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
            'kategori'       => upper_first(clear($this->request->getVar('kategori'))),
            'value'       => upper_first(clear($this->request->getVar('value')))
        ];

        if ((db(menu()['tabel'])->whereNotIn('id', [$id]))->where("kategori", $q['kategori'])->where("value", $q['value'])->get()->getRowArray()) {
            gagal(base_url(menu()['controller']), "Value existed");
        }

        // Simpan data
        db(menu()['tabel'])->where('id', $id)->update($q)
            ? sukses(base_url(menu()['controller']), 'Sukses')
            : gagal(base_url(menu()['controller']), 'Gagal');
    }
}
