<?php

namespace App\Controllers;

class Transaksi extends BaseController
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

        $data = db(menu()['tabel'])->orderBy("tgl", "DESC")->get()->getResultArray();
        return view(menu()['controller'] . '/' . menu()['controller'] . "_" . 'landing', ['judul' => menu()['menu'], "data" => $data]);
    }

    public function bayar()
    {
        $super_total = json_decode(json_encode($this->request->getVar('super_total')), true);
        $datas = json_decode(json_encode($this->request->getVar('datas')), true);
        $uang = angka_to_int(clear($this->request->getVar('uang')));

        if ($uang < $super_total['biaya']) {
            gagal_js("Uang kurang");
        }

        $db = \Config\Database::connect();
        $db->transStart();

        $no_nota = next_invoice();

        $tgl = time();

        foreach ($datas as $i) {
            if (!$db->table('transaksi')->insert([
                "tgl" => $tgl,
                "jenis" => $i['jenis'],
                "barang" => $i['barang'],
                "barang_id" => $i['id'],
                "harga" => $i['harga'],
                "qty" => $i['qty'],
                "total" => $i['total'],
                "diskon" => $i['diskon'],
                "biaya" => $i['biaya'],
                "karyawan" => $i['karyawan'],
                "petugas" => user()['nama']
            ])) {
                gagal_js("Insert transaksi gagal");
            }

            $barang = db('barang')->where('id', $i['id'])->get()->getRowArray();
            if (!$barang) {
                gagal_js("Id " . $i['barang'] . " not found");
            }
            if ($barang['jenis'] !== "Layanan") {
                $barang['qty'] -= (int)$i['qty'];
                if (!db('barang')->where('id', $barang['id'])->update($barang)) {
                    gagal_js("Update stok gagal");
                }
            }

            if (!$db->table('nota')->insert([
                "no_nota" => $no_nota,
                "tgl" => $tgl,
                "jenis" => $i['jenis'],
                "barang" => $i['barang'],
                "barang_id" => $i['id'],
                "harga" => $i['harga'],
                "qty" => $i['qty'],
                "total" => $i['total'],
                "diskon" => $i['diskon'],
                "biaya" => $i['biaya'],
                "petugas" => user()['nama'],
                "uang" => $uang,
            ])) {
                gagal_js("Insert nota gagal");
            }
        }


        $db->transComplete();

        return $db->transStatus()
            ? sukses_js("Sukses", str_replace("/", "-", $no_nota))
            : gagal_js("Gagal");
    }
    public function add_hutang()
    {
        $datas = json_decode(json_encode($this->request->getVar('datas')), true);
        $nama = upper_first(clear($this->request->getVar('nama')));
        $id = clear($this->request->getVar('id'));
        $db = \Config\Database::connect();
        $db->transStart();

        $nota = next_invoice("hutang");

        $tgl = time();

        foreach ($datas as $i) {
            $db->table('hutang')->insert([
                "no_nota" => $nota,
                "tgl" => $tgl,
                "jenis" => $i['jenis'],
                "barang" => $i['barang'],
                "barang_id" => $i['id'],
                "harga" => $i['harga'],
                "qty" => $i['qty'],
                "total" => $i['total'],
                "diskon" => $i['diskon'],
                "biaya" => $i['biaya'],
                "petugas" => user()['nama'],
                "nama" => $nama,
                "user_id" => $id
            ]);

            $barang = db('barang')->where('id', $i['id'])->get()->getRowArray();
            if (!$barang) {
                gagal_js("Id " . $i['barang'] . " not found");
            }
            $barang['qty'] -= (int)$i['qty'];
            db('barang')->where('id', $barang['id'])->update($barang);
        }


        $db->transComplete();

        return $db->transStatus()
            ? sukses_js("Sukses")
            : gagal_js("Gagal");
    }


    public function cari_user()
    {
        $text = clear($this->request->getVar("text"));
        $roles = json_decode(json_encode($this->request->getVar("roles")), true);
        $data = db('user')->whereIn('role', $roles)->like("nama", $text, "both")->orderBy('nama', 'ASC')->limit(7)->get()->getResultArray();

        sukses_js("Ok", $data);
    }
    public function cari_barang()
    {
        $text = clear($this->request->getVar("text"));
        $jenis = json_decode(json_encode($this->request->getVar("jenis")), true);
        $data = db('barang')->whereIn('jenis', $jenis)->like("barang", $text, "both")->orderBy('barang', 'ASC')->limit(7)->get()->getResultArray();

        sukses_js("Ok", $data);
    }
    public function add_user()
    {
        $input = [
            "nama" => upper_first(clear($this->request->getVar("nama"))),
            "wa" => clear($this->request->getVar("wa")),
            "role" => "Member",
            "username" => random_string(4),
            "password" => password_hash(settings("password")['value'], PASSWORD_DEFAULT)
        ];

        db("user")->insert($input)
            ? sukses_js('Sukses')
            : gagal_js('Gagal');
    }

    public function list()
    {
        $tahun = clear($this->request->getVar('tahun'));
        $bulan = clear($this->request->getVar('bulan'));
        $jenis = clear($this->request->getVar('jenis'));

        // Query total biaya
        $total = db(strtolower($jenis))
            ->selectSum('biaya')
            ->where("MONTH(FROM_UNIXTIME(tgl))", $bulan)
            ->where("YEAR(FROM_UNIXTIME(tgl))", $tahun)
            ->get()
            ->getRowArray();


        // Query data detail
        $data = db(strtolower($jenis))
            ->select('*')
            ->where("MONTH(FROM_UNIXTIME(tgl))", $bulan)
            ->where("YEAR(FROM_UNIXTIME(tgl))", $tahun)
            ->orderBy('tgl', 'DESC')
            ->get()
            ->getResultArray();


        sukses_js("Ok", $data, $total['biaya']);
    }
}
