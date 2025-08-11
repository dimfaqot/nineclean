<?php

namespace App\Controllers;

class Menu extends BaseController
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
        $data = db(menu()['tabel'])->orderBy("urutan", "ASC")->get()->getResultArray();
        return view(menu()['controller'] . '/' . menu()['controller'] . "_" . 'landing', ['judul' => menu()['menu'], "data" => $data]);
    }
    public function add()
    {
        $input = [
            'role'       => clear($this->request->getVar('role')),
            'menu'       => upper_first(clear($this->request->getVar('menu'))),
            'tabel'      => strtolower(clear($this->request->getVar('tabel'))),
            'controller' => clear($this->request->getVar('controller')),
            'icon'       => strtolower(clear($this->request->getVar('icon'))),
            'grup'       => upper_first(clear($this->request->getVar('grup')))
        ];

        if ($input['role'] == "") {
            gagal(base_url(menu()['controller']), "Role failed");
        }
        // Cek duplikat
        if (db(menu()['tabel'])->where('role', $input['role'])->where('menu', $input['menu'])->countAllResults() > 0) {
            gagal(base_url(menu()['controller']), 'Menu existed');
        }

        // Dapatkan urutan terakhir
        $last = db(menu()['tabel'])->select('urutan')->orderBy('urutan', 'DESC')->get()->getRowArray();
        $input['urutan'] = isset($last['urutan']) ? $last['urutan'] + 1 : 1;

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
            'urutan'     => clear($this->request->getVar('urutan')),
            'menu'       => upper_first(clear($this->request->getVar('menu'))),
            'tabel'      => strtolower(clear($this->request->getVar('tabel'))),
            'controller' => clear($this->request->getVar('controller')),
            'icon'       => strtolower(clear($this->request->getVar('icon'))),
            'grup'       => upper_first(clear($this->request->getVar('grup')))
        ];

        if ((db(menu()['tabel'])->whereNotIn('id', [$id]))->where("menu", $q['menu'])->get()->getRowArray()) {
            gagal(base_url(menu()['controller']), "Menu existed");
        }
        if ((db(menu()['tabel'])->whereNotIn('id', [$id]))->where("urutan", $q['urutan'])->get()->getRowArray()) {
            gagal(base_url(menu()['controller']), "Urutan existed");
        }

        // Simpan data
        db(menu()['tabel'])->where('id', $id)->update($q)
            ? sukses(base_url(menu()['controller']), 'Sukses')
            : gagal(base_url(menu()['controller']), 'Gagal');
    }
    public function copy()
    {
        $menu_id = clear($this->request->getVar('menu_id'));
        $role = clear($this->request->getVar('role'));

        $q = db(menu()['tabel'])->where('id', $menu_id)->get()->getRowArray();

        if (!$q) {
            gagal_js("Id menu not found");
        }

        $exist = db(menu()['tabel'])->where('role', $role)->where("menu", $q['menu'])->get()->getRowArray();

        if ($exist) {
            gagal_js("Menu exits in role");
        }


        $q['role'] = $role;

        // Dapatkan urutan terakhir
        $last = db(menu()['tabel'])->select('urutan')->orderBy('urutan', 'DESC')->get()->getRowArray();
        $q['urutan'] = isset($last['urutan']) ? $last['urutan'] + 1 : 1;
        unset($q['id']);

        // Simpan data
        db(menu()['tabel'])->insert($q)
            ? sukses_js('Sukses')
            : gagal_js('Gagal');
    }
}
