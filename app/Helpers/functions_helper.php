<?php

function db($tabel, $db = null)
{
    if ($db == null || $db == 'batea') {
        $db = \Config\Database::connect();
    } else {
        $db = \Config\Database::connect(strtolower(str_replace(" ", "_", $db)));
    }
    $db = $db->table($tabel);

    return $db;
}

function clear($text)
{
    $text = trim($text);
    $text = htmlspecialchars($text);
    return $text;
}

function upper_first($text)
{
    $text = clear($text);
    $exp = explode(" ", $text);

    $val = [];
    foreach ($exp as $i) {
        $lower = strtolower($i);
        $val[] = ucfirst($lower);
    }

    return implode(" ", $val);
}

function sukses($url, $pesan)
{
    session()->setFlashdata('sukses', $pesan);
    header("Location: " . $url);
    die;
}

function gagal($url, $pesan)
{
    session()->setFlashdata('gagal', $pesan);
    header("Location: " . $url);
    die;
}

function gagal_js($pesan, $data = null, $data2 = null, $data3 = null, $data4 = null, $data5 = null)
{
    $res = [
        'status' => '400',
        'message' =>  $pesan,
        'data' => $data,
        'data2' => $data2,
        'data3' => $data3,
        'data4' => $data4,
        'data5' => $data5
    ];

    echo json_encode($res);
    die;
}

function sukses_js($pesan, $data = null, $data2 = null, $data3 = null, $data4 = null, $data5 = null)
{
    $data = [
        'status' => '200',
        'message' => $pesan,
        'data' => $data,
        'data2' => $data2,
        'data3' => $data3,
        'data4' => $data4,
        'data5' => $data5
    ];

    echo json_encode($data);
    die;
}

function options($kategori)
{
    $q = db('options')->where("kategori", upper_first($kategori))->orderBy("value", "ASC")->get()->getResultArray();

    return $q;
}

function url()
{
    $url = service('uri');
    $res = $url->getPath();
    $res = str_replace("index.php/", "", $res);
    $res = explode("/", $res);
    $res = ($res[0] == "" ? $res[1] : $res[0]);
    return $res;
}

function user()
{
    $res = false;
    if (session('id')) {
        $res = db('user')->where('id', session('id'))->get()->getRowArray();
    }
    return $res;
}

function menus()
{

    $items = db('menu')
        ->where('role', (user() ? user()['role'] : "Public"))
        ->orderBy('urutan', 'ASC')
        ->orderBy('menu', 'ASC')
        ->get()
        ->getResultArray();

    $data = [];

    foreach ($items as $item) {
        $data[$item['grup']][] = $item;
    }

    // Jika perlu format seperti sebelumnya:
    $result = [];
    foreach ($data as $grup => $list) {
        $menus = [];

        foreach ($list as $i) {
            $menus[] = $i['controller'];
        }

        $result[] = ['grup' => $grup, 'data' => $list, "menus" => $menus];
    }

    return $result;
}

function menu($controller = null)
{
    $controller = ($controller == "" ? url() : $controller);
    $controller = ($controller == null ? url() : $controller);

    $q = db('menu')->where('role', (user() ? user()['role'] : "Public"))->where('controller', $controller)->get()->getRowArray();

    if (!$q) {
        gagal(base_url("home"), "Access denied");
    } else {
        return $q;
    }
}

function settings($nama)
{
    return db('settings')->where('nama', strtolower($nama))->get()->getRowArray();
}

function angka($uang)
{
    return number_format($uang, 0, ",", ".");
}

function angka_to_int($uang)
{
    $uang = str_replace("Rp. ", "", $uang);
    $uang = str_replace(".", "", $uang);
    return $uang;
}

function barang($jenis = null)
{
    $db = db('barang');
    if ($jenis !== null) {
        $db->whereIn("jenis", $jenis);
    }
    return $db->orderBy("barang", "ASC")->get()->getResultArray();
}

function random_string($length = 14)
{
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}

function next_invoice($order = null)
{

    $db = db('nota');

    $year  = date('Y');
    $month = date('m');
    $prefix = "$year/$month/";

    // Cari no_nota terakhir berdasarkan bulan ini
    $lastNota = $db->select('no_nota')
        ->orderBy('tgl', 'DESC')
        ->get()
        ->getRowArray();

    if ($order == "inv") {
        $lastNota = $db->select('inv')
            ->orderBy('tgl', 'DESC')
            ->get()
            ->getRowArray();
    }


    $nextNumber = 1;
    if ($lastNota) {
        $parts = explode('/', $lastNota['no_nota']);
        $lastNumber = end($parts);
        $nextNumber = (int)$lastNumber + 1;
    }

    $nota = $prefix . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);

    if ($order == "hutang") {
        $nota = $prefix . random_string(6);
    }

    return $nota;
}

function users($roles = null)
{
    $db = db('user');

    $db;
    if ($roles !== null) {
        $db->whereIn('role', $roles);
    }
    $q = $db->orderBy('nama', 'ASC')->get()->getResultArray();
    return $q;
}

function tahuns($tabel)
{
    $db = db($tabel);
    $db->select("YEAR(FROM_UNIXTIME(tgl)) AS tahun");
    $db->groupBy("tahun");
    $db->orderBy("tahun", "ASC");

    $query = $db->get();
    $results = $query->getResultArray();
    return $results;
}

function bulans($req = null)
{
    $bulan = [
        ['romawi' => 'I', 'bulan' => 'Januari', 'angka' => '01', 'satuan' => 1],
        ['romawi' => 'II', 'bulan' => 'Februari', 'angka' => '02', 'satuan' => 2],
        ['romawi' => 'III', 'bulan' => 'Maret', 'angka' => '03', 'satuan' => 3],
        ['romawi' => 'IV', 'bulan' => 'April', 'angka' => '04', 'satuan' => 4],
        ['romawi' => 'V', 'bulan' => 'Mei', 'angka' => '05', 'satuan' => 5],
        ['romawi' => 'VI', 'bulan' => 'Juni', 'angka' => '06', 'satuan' => 6],
        ['romawi' => 'VII', 'bulan' => 'Juli', 'angka' => '07', 'satuan' => 7],
        ['romawi' => 'VIII', 'bulan' => 'Agustus', 'angka' => '08', 'satuan' => 8],
        ['romawi' => 'IX', 'bulan' => 'September', 'angka' => '09', 'satuan' => 9],
        ['romawi' => 'X', 'bulan' => 'Oktober', 'angka' => '10', 'satuan' => 10],
        ['romawi' => 'XI', 'bulan' => 'November', 'angka' => '11', 'satuan' => 11],
        ['romawi' => 'XII', 'bulan' => 'Desember', 'angka' => '12', 'satuan' => 12]
    ];

    $res = $bulan;
    foreach ($bulan as $i) {
        if ($i['bulan'] == $req) {
            $res = $i;
        } elseif ($i['angka'] == $req) {
            $res = $i;
        } elseif ($i['satuan'] == $req) {
            $res = $i;
        } elseif ($i['romawi'] == $req) {
            $res = $i;
        }
    }
    return $res;
}

function profile()
{
    return db('profile')->get()->getRowArray();
}

function uang_modal()
{
    // Query total biaya
    $total = db('pengeluaran')
        ->selectSum('biaya')
        ->where('jenis', "Modal")
        ->get()
        ->getRowArray();

    // Query data detail
    $data = db('pengeluaran')
        ->select('*')
        ->where('jenis', "Modal")
        ->orderBy('tgl', 'DESC')
        ->get()
        ->getResultArray();

    $res = ['total' => $total['biaya'], 'data' => $data];

    return $res;
}
