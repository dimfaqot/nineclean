<?php

namespace App\Controllers;

class Barang extends BaseController
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
        $data = db(menu()['tabel'])->orderBy("barang", "ASC")->get()->getResultArray();
        return view(menu()['controller'] . '/' . menu()['controller'] . "_" . 'landing', ['judul' => menu()['menu'], "data" => $data]);
    }
    public function add()
    {
        $input = [
            'jenis'      => angka_to_int(clear($this->request->getVar('jenis'))),
            'barang'       => upper_first(clear($this->request->getVar('barang'))),
            'qty'       => 0,
            'harga'      => angka_to_int(clear($this->request->getVar('harga')))
        ];

        $qty = angka_to_int(clear($this->request->getVar('qty')));
        if ($qty !== "") {
            $input['qty'] = $qty;
        }

        // Cek duplikat
        if (db(menu()['tabel'])->where('barang', $input['barang'])->countAllResults() > 0) {
            gagal(base_url(menu()['controller']), 'Barang existed');
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
            'jenis'      => angka_to_int(clear($this->request->getVar('jenis'))),
            'barang'       => upper_first(clear($this->request->getVar('barang'))),
            'harga'      => angka_to_int(clear($this->request->getVar('harga')))
        ];

        $qty = angka_to_int(clear($this->request->getVar('qty')));
        if ($qty !== "") {
            $q['qty'] = $qty;
        }

        if ((db(menu()['tabel'])->whereNotIn('id', [$id]))->where("barang", $q['barang'])->get()->getRowArray()) {
            gagal(base_url(menu()['controller']), "Barang existed");
        }

        // Simpan data
        db(menu()['tabel'])->where('id', $id)->update($q)
            ? sukses(base_url(menu()['controller']), 'Sukses')
            : gagal(base_url(menu()['controller']), 'Gagal');
    }
}
