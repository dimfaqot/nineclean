<?php

namespace App\Controllers;

class Pengeluaran extends BaseController
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
        $total = db('pengeluaran')
            ->selectSum('biaya')
            ->whereNotIn('jenis', ['Modal', 'Inv'])
            ->where("MONTH(FROM_UNIXTIME(tgl))", date('n'))
            ->where("YEAR(FROM_UNIXTIME(tgl))", date('Y'))
            ->orderBy('updated_at', 'DESC')
            ->get()
            ->getRowArray();

        // Query data detail
        $data = db('pengeluaran')
            ->select('*')
            ->whereNotIn('jenis', ['Modal', 'Inv'])
            ->where("MONTH(FROM_UNIXTIME(tgl))", date('n'))
            ->where("YEAR(FROM_UNIXTIME(tgl))", date('Y'))
            ->orderBy('updated_at', 'DESC')
            ->orderBy('tgl', 'DESC')
            ->get()
            ->getResultArray();

        return view(menu()['controller'] . '/' . menu()['controller'] . "_" . 'landing', ['judul' => menu()['menu'], "data" => $data, 'total' => $total['biaya']]);
    }
    public function add()
    {
        $barang_id = clear($this->request->getVar('barang_id'));
        $harga = angka_to_int(clear($this->request->getVar('harga')));
        $qty = angka_to_int(clear($this->request->getVar('qty')));
        $diskon = angka_to_int(clear($this->request->getVar('diskon')));
        $pj = upper_first(clear($this->request->getVar('pj')));

        $db = \Config\Database::connect();
        $db->transStart();

        $barang = db('barang')->where('id', $barang_id)->get()->getRowArray();

        if ($diskon > ($harga * $qty)) {
            gagal(base_url(menu()['controller']), "Diskon over");
        }
        if (!$barang) {
            gagal(base_url(menu()['controller']), "Barang not found");
        }

        $input = [

            'tgl' => time(),
            'jenis' => $barang['jenis'],
            'barang' => $barang['barang'],
            'barang_id' => $barang['id'],
            'harga'       => angka_to_int(clear($this->request->getVar('harga'))),
            'qty'       => $qty,
            'total'       => $harga * $qty,
            'diskon'       => $diskon,
            'biaya'       => ($harga * $qty) - $diskon,
            'pj'       => $pj,
            'petugas'       => user()['nama'],
            'updated_at'       => time()
        ];

        if ($barang['jenis'] !== "Kulakan") {
            $barang['qty'] += (int)$input['qty'];
            db('barang')->where('id', $barang['id'])->update($barang);
        }

        // Simpan data  
        db(menu()['tabel'])->insert($input);

        $db->transComplete();

        return $db->transStatus()
            ? sukses(base_url(menu()['controller']), 'Sukses')
            : gagal(base_url(menu()['controller']), 'Gagal');
    }

    public function edit()
    {
        $id = clear($this->request->getVar('id'));
        $barang_id = clear($this->request->getVar('barang_id'));
        $harga = angka_to_int(clear($this->request->getVar('harga')));
        $qty = angka_to_int(clear($this->request->getVar('qty')));
        $diskon = angka_to_int(clear($this->request->getVar('diskon')));
        $pj = upper_first(clear($this->request->getVar('pj')));

        $db = db_connect();
        $db->transStart();

        // Ambil data lama
        $data_lama = db(menu()['tabel'])->where('id', $id)->get()->getRowArray();
        $barang    = db('barang')->where('id', $barang_id)->get()->getRowArray();

        if (!$data_lama) return gagal(base_url(menu()['controller']), "Id not found");
        if (!$barang)    return gagal(base_url(menu()['controller']), "Barang not found");
        if ($diskon > ($harga * $qty)) return gagal(base_url(menu()['controller']), "Diskon over");

        // Update stok jika qty berubah
        if ($barang['jenis'] !== "Layanan") {
            if ($data_lama['qty'] != $qty) {
                $barang['qty'] = ($barang['qty'] - $data_lama['qty']) + $qty;
                if (!db('barang')->where('id', $barang['id'])->update($barang)) {
                    return gagal(base_url(menu()['controller']), "Update qty gagal");
                }
            }
        }

        // Siapkan data baru
        $data_baru = [
            'jenis'      => $barang['jenis'],
            'barang'     => $barang['barang'],
            'barang_id'  => $barang['id'],
            'harga'      => $harga,
            'qty'        => $qty,
            'total'      => $harga * $qty,
            'diskon'     => $diskon,
            'biaya'      => ($harga * $qty) - $diskon,
            'pj'         => $pj,
            'petugas'    => user()['nama'],
            'updated_at' => time()
        ];

        // Update transaksi
        $update = db(menu()['tabel'])->where('id', $id)->update($data_baru);

        $db->transComplete();

        if (!$db->transStatus() || !$update) {
            return gagal(base_url(menu()['controller']), 'Update gagal');
        }

        return sukses(base_url(menu()['controller']), 'Sukses');
    }

    public function list()
    {
        $tahun = clear($this->request->getVar('tahun'));
        $bulan = clear($this->request->getVar('bulan'));

        // Query total biaya
        $total = db('pengeluaran')
            ->selectSum('biaya')
            ->whereNotIn('jenis', ["Inv", "modal"])
            ->where("MONTH(FROM_UNIXTIME(tgl))", $bulan)
            ->where("YEAR(FROM_UNIXTIME(tgl))", $tahun)
            ->get()
            ->getRowArray();


        // Query data detail
        $data = db('pengeluaran')
            ->select('*')
            ->whereNotIn('jenis', ["Inv", "modal"])
            ->where("MONTH(FROM_UNIXTIME(tgl))", $bulan)
            ->where("YEAR(FROM_UNIXTIME(tgl))", $tahun)
            ->orderBy('tgl', 'DESC')
            ->get()
            ->getResultArray();


        sukses_js("Ok", $data, $total['biaya']);
    }
}
