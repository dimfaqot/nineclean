<?php

namespace App\Controllers;

class Inv extends BaseController
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

        return view(menu()['controller'] . '/' . menu()['controller'] . "_" . 'landing', ['judul' => menu()['menu']]);
    }
    public function add()
    {
        $datas = json_decode(json_encode($this->request->getVar('datas')), true);
        $pj = upper_first(clear($this->request->getVar('pj')));
        $jenis = upper_first(clear($this->request->getVar('jenis')));

        $db = \Config\Database::connect();
        $db->transStart();

        $tgl = time();

        foreach ($datas as $i) {
            $db->table('pengeluaran')->insert([
                'tgl' => $tgl,
                'jenis' => $jenis,
                'barang' => $i['barang'],
                'barang_id' => 0,
                'harga'     => angka_to_int($i['harga']),
                'qty'       => angka_to_int($i['qty']),
                'total'       => angka_to_int($i['harga']) * angka_to_int($i['qty']),
                'diskon'       => angka_to_int($i['diskon']),
                'biaya'       => (angka_to_int($i['harga']) * angka_to_int($i['qty'])) - angka_to_int($i['diskon']),
                'pj'       => $pj,
                'petugas'       => user()['nama'],
                'updated_at'       => $tgl
            ]);
        }

        $db->transComplete();

        return $db->transStatus()
            ? sukses_js("Sukses")
            : gagal_js("Gagal");
    }

    public function edit()
    {
        $id = clear($this->request->getVar('id'));

        $q = db('pengeluaran')->where('id', $id)->get()->getRowArray();

        if (!$q) {
            gagal(base_url(menu()['controller']), "Id not found");
        }

        $q = [
            'jenis'       => clear($this->request->getVar('jenis')),
            'barang'       => clear($this->request->getVar('barang')),
            'harga'      => angka_to_int(clear($this->request->getVar('harga'))),
            'qty'       => angka_to_int(clear($this->request->getVar('qty'))),
            'total'       => angka_to_int(clear($this->request->getVar('total'))),
            'diskon'       => angka_to_int(clear($this->request->getVar('diskon'))),
            'biaya'       => angka_to_int(clear($this->request->getVar('biaya'))),
            'pj'       => upper_first(clear($this->request->getVar('pj'))),
            'petugas'       => user()['nama'],
            'updated_at'       => time()
        ];

        // Simpan data
        if (db('pengeluaran')->where('id', $id)->update($q)) {
            $tahun = clear($this->request->getVar('tahun'));
            $bulan = clear($this->request->getVar('bulan'));

            $total = db('pengeluaran')
                ->selectSum('biaya')
                ->where('jenis', 'Inv')
                ->where("MONTH(FROM_UNIXTIME(tgl))", $bulan)
                ->where("YEAR(FROM_UNIXTIME(tgl))", $tahun)
                ->get()
                ->getRowArray();

            // Query data detail
            $data = db('pengeluaran')
                ->select('*')
                ->where('jenis', 'Inv')
                ->where("MONTH(FROM_UNIXTIME(tgl))", $bulan)
                ->where("YEAR(FROM_UNIXTIME(tgl))", $tahun)
                ->orderBy('tgl', 'DESC')
                ->get()
                ->getResultArray();


            sukses_js("Ok", $data, $total['biaya']);
        } else {
            gagal_js("Ggaal");
        }
    }

    public function list()
    {
        $tahun = clear($this->request->getVar('tahun'));
        $bulan = clear($this->request->getVar('bulan'));
        $jenis = clear($this->request->getVar('jenis'));

        // Query total biaya
        $total = db('pengeluaran')
            ->selectSum('biaya')
            ->where('jenis', $jenis)
            ->where("MONTH(FROM_UNIXTIME(tgl))", $bulan)
            ->where("YEAR(FROM_UNIXTIME(tgl))", $tahun)
            ->get()
            ->getRowArray();

        // Query data detail
        $data = db('pengeluaran')
            ->select('*')
            ->where('jenis', $jenis)
            ->where("MONTH(FROM_UNIXTIME(tgl))", $bulan)
            ->where("YEAR(FROM_UNIXTIME(tgl))", $tahun)
            ->orderBy('tgl', 'DESC')
            ->get()
            ->getResultArray();


        sukses_js("Ok", $data, $total['biaya']);
    }
}
