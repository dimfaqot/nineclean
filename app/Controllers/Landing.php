<?php

namespace App\Controllers;

class Landing extends BaseController
{
    public function index(): string
    {

        if (session('id')) {
            sukses(base_url("home"), "You are logged");
        }

        return view('guest/landing', ['judul' => "BKW"]);
    }

    public function auth()
    {
        $username = clear($this->request->getVar('username'));
        $password = clear($this->request->getVar('password'));

        $user = db('user', 'bkw')->where('username', $username)->get()->getRowArray();

        if (!$user) {
            gagal(base_url(), "Username not found");
        }

        if (!password_verify($password, $user['password'])) {
            gagal(base_url(), "Password salah");
        }

        $db = explode(",", $user['db']);

        $data = [
            'id' => $user['id'],
            'dbs' => $db,
            'db' => (count($db) == 1 ? $user['db'] : '')
        ];
        if ($user['lokasi'] !== "") {
            $data['lokasi'] = $user['lokasi'];
        }

        session()->set($data);
        sukses(base_url('home'), 'Login sukses.');
    }

    public function logout()
    {
        session()->destroy();
        session()->setFlashdata('sukses', "Logout sukses");
        header("Location: " . base_url());
        die;
    }
}
