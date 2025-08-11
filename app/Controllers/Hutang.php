<?php

namespace App\Controllers;

class Hutang extends BaseController
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

        $data = db('hutang')
            ->select('*')
            ->selectSum('biaya', 'total_biaya')
            ->groupBy('user_id')
            ->get()
            ->getResultArray();

        return view(menu()['controller'] . '/' . menu()['controller'] . "_" . 'landing', ['judul' => menu()['menu'], "data" => $data]);
    }

    public function detail()
    {
        $user_id = clear($this->request->getVar('user_id'));

        $data = db('hutang')->where('user_id', $user_id)->orderBy('tgl', "ASC")->get()->getResultArray();

        sukses_js("Ok", $data);
    }
    public function wa()
    {
        $user_id = clear($this->request->getVar('user_id'));
        $user = db('user')->where('id', $user_id)->get()->getRowArray();

        if (!$user) {
            gagal_js("User not found");
        }

        $data = db('hutang')->where('user_id', $user_id)->orderBy('tgl', "ASC")->get()->getResultArray();

        sukses_js("Ok", $data, $user);
    }
    public function kasir()
    {
        $user_id = clear($this->request->getVar('user_id'));

        $data = db('hutang')->where('user_id', $user_id)->orderBy('tgl', "ASC")->get()->getResultArray();

        $super_total = ['total' => 0, "diskon" => 0, "biaya" => 0];
        foreach ($data as $i) {
            $super_total['total'] += (int)$i['total'];
            $super_total['diskon'] += (int)$i['diskon'];
            $super_total['biaya'] += (int)$i['biaya'];
        }

        sukses_js("Ok", $super_total);
    }

    public function bayar()
    {
        $user_id = clear($this->request->getVar('user_id'));
        $uang = angka_to_int(clear($this->request->getVar('uang')));
        $biaya = angka_to_int(clear($this->request->getVar('biaya')));
        $data = db('hutang')->where('user_id', $user_id)->orderBy('tgl', "ASC")->get()->getResultArray();

        if ($uang < $biaya) {
            gagal_js("Uang kurang");
        }

        $db = \Config\Database::connect();
        $db->transStart();

        $no_nota = next_invoice();

        $tgl = time();

        foreach ($data as $i) {
            if (!$db->table('transaksi')->insert([
                "tgl" => $tgl,
                "jenis" => $i['jenis'],
                "barang" => $i['barang'],
                "barang_id" => $i['barang_id'],
                "harga" => $i['harga'],
                "qty" => $i['qty'],
                "total" => $i['total'],
                "diskon" => $i['diskon'],
                "biaya" => $i['biaya'],
                "petugas" => user()['nama']
            ])) {
                gagal_js("Insert transaksi gagal");
            }

            if (!$db->table('nota')->insert([
                "no_nota" => $no_nota,
                "tgl" => $tgl,
                "jenis" => $i['jenis'],
                "barang" => $i['barang'],
                "barang_id" => $i['barang_id'],
                "harga" => $i['harga'],
                "qty" => $i['qty'],
                "total" => $i['total'],
                "diskon" => $i['diskon'],
                "biaya" => $i['biaya'],
                "petugas" => user()['nama'],
                "uang" => $uang
            ])) {
                gagal_js("Insert nota gagal");
            }

            if (!db('hutang')->where('id', $i['id'])->delete()) {
                gagal_js($i['barang'] . " gagal dihapus");
            }
        }


        $db->transComplete();

        return $db->transStatus()
            ? sukses_js("Sukses", str_replace("/", "-", $no_nota))
            : gagal_js("Gagal");
    }
}
