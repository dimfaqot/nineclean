<?php

namespace App\Controllers;

class User extends BaseController
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
        return view(menu()['controller'] . '/' . menu()['controller'] . "_" . 'landing', ['judul' => menu()['menu'], "data" => $data]);
    }
    public function add()
    {

        $input = [
            'role'       => clear($this->request->getVar('role')),
            'nama'       => upper_first(clear($this->request->getVar('nama'))),
            'username'       => strtolower(clear($this->request->getVar('username'))),
            'wa'       => clear($this->request->getVar('wa')),
            'password'       => password_hash(settings("password")['value'], PASSWORD_DEFAULT)
        ];


        // Cek duplikat
        if (db(menu()['tabel'])->where('username', $input['username'])->countAllResults() > 0) {
            gagal(base_url(menu()['controller']), 'Username existed');
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
            'role'       => clear($this->request->getVar('role')),
            'nama'       => upper_first(clear($this->request->getVar('nama'))),
            'username'       => strtolower(clear($this->request->getVar('username'))),
            'wa'       => clear($this->request->getVar('wa'))
        ];

        if ($this->request->getVar("password") !== "") {
            $q['password'] = password_hash(settings("password")['value'], PASSWORD_DEFAULT);
        }

        if ((db(menu()['tabel'])->whereNotIn('id', [$id]))->where("username", $q['username'])->get()->getRowArray()) {
            gagal(base_url(menu()['controller']), "Username existed");
        }


        // Simpan data
        db(menu()['tabel'])->where('id', $id)->update($q)
            ? sukses(base_url(menu()['controller']), 'Sukses')
            : gagal(base_url(menu()['controller']), 'Gagal');
    }
}
