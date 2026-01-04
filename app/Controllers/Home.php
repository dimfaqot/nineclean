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
        $data = db('options', 'bkw')->where('kategori', 'Lokasi')->where('db', session('db'))->orderBy('value', 'ASC')->get()->getResultArray();
        return view(menu()['controller'] . '/' . menu()['controller'] . '_' . 'landing', ['judul' => menu()['menu'], 'data' => $data]);
    }

    public function check_db()
    {
        $db = strtolower(clear($this->request->getVar('db')));

        $q = db('options', 'bkw')->where('kategori', 'Lokasi')->where('db', $db)->orderBy('value', 'ASC')->get()->getResultArray();

        sukses_js("Ok", $q);
    }
    public function change_db()
    {
        $db = strtolower(clear($this->request->getVar('db')));
        $lokasi = clear($this->request->getVar('lokasi'));

        if (!in_array($db, session('dbs'))) {
            gagal_js('Not allowed');
        }

        session()->set('db', $db);

        $q = db('options', 'bkw')->where('kategori', 'Lokasi')->where('db', $db)->get()->getResultArray();

        if (user()['role'] == "Root") {
            if (count($q) > 0 && $lokasi !== "") {
                session()->set('lokasi', $lokasi);
            } else {
                session()->remove('lokasi');
            }
        } else {
            if (user()['lokasi'] !== "" && count($q) > 0) {
                session()->set('lokasi', $lokasi);
            } else {
                session()->remove('lokasi');
            }
        }

        sukses_js("Sukses");
    }

    public function encode_jwt()
    {
        $data = json_decode(json_encode($this->request->getVar('data')), true);
        $data['login'] = session('id');
        $data['petugas'] = user()['nama'];
        $data['db'] = session('db');
        $data['admin'] = user()['role'];
        $data['time'] = time();
        if (session()->has('lokasi')) {
            $data['lokasi'] = session('lokasi');
        }
        sukses_js("Ok", encode_jwt($data), decode_jwt(encode_jwt($data)));
    }
}
