<?php

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

function db($tabel, $db = null)
{
    if ($db == null || $db == 'nineclean') {
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
        $res = db('user', 'bkw')->where('id', session('id'))->get()->getRowArray();
    }
    return $res;
}

function menus()
{
    $dbi = db('menu', 'bkw');
    if (session('db') !== "") {
        $dbi->where('db', session('db'));
    } else {
        $dbi->where('menu', 'Home');
    }
    $items = $dbi->where('role', (user() ? user()['role'] : "Public"))
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

    $db = db('menu', 'bkw');
    if (session('db') !== "") {
        $db->where('db', session('db'));
    }

    $q = $db->where('role', (user() ? user()['role'] : "Public"))->where('controller', $controller)->get()->getRowArray();

    if (!$q) {
        gagal(base_url("home"), "Access denied");
    } else {
        return $q;
    }
}


function encode_jwt($data)
{

    $jwt = JWT::encode($data, getenv("KEY_JWT"), 'HS256');

    return $jwt;
}

function decode_jwt($encode_jwt)
{
    try {

        $decoded = JWT::decode($encode_jwt, new Key(getenv("KEY_JWT"), 'HS256'));
        $arr = (array)$decoded;

        return $arr;
    } catch (\Exception $e) { // Also tried JwtException
        $data = [
            'status' => '400',
            'message' => $e->getMessage()
        ];

        echo json_encode($data);
        die;
    }
}

function get_dbs()
{
    $db = \Config\Database::connect();
    $query = $db->query("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA ORDER BY SCHEMA_NAME ASC");
    $result = $query->getResultArray();


    $res = [];
    foreach ($result as $i) {
        $exp = explode("_", $i['SCHEMA_NAME']);
        $res[] = end($exp);
    }
    sort($res);
    return $res;
}
