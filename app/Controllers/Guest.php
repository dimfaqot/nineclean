<?php

namespace App\Controllers;

class Guest extends BaseController
{
    public function nota($no_nota)
    {
        $no_nota = str_replace("-", '/', $no_nota);

        $set = [
            'mode' => 'utf-8',
            'format' => [50, 120],
            'orientation' => 'P',
            'margin_left' => 0,
            'margin_right' => 0,
            'margin_top' => 0,
            'margin_bottom' => 0
        ];

        $data = db('nota')->where('no_nota', $no_nota)->get()->getResultArray();
        $mpdf = new \Mpdf\Mpdf($set);
        $mpdf->SetAutoPageBreak(false);

        $judul = "NOTA " . $no_nota;
        // Dapatkan konten HTML
        // $logo = '<img width="90" src="logo.png" alt="KOP"/>';
        $html = view('guest/nota', ['judul' => $judul, 'data' => $data, 'no_nota' => $no_nota]); // view('pdf_template') mengacu pada file view yang akan dirender menjadi PDF

        // Setel konten HTML ke mPDF
        $mpdf->WriteHTML($html);

        // Output PDF ke browser
        $this->response->setHeader('Content-Type', 'application/pdf');
        $mpdf->Output($judul . '.pdf', 'I');
    }

    public function login()
    {
        $username = strtolower(clear($this->request->getVar('username')));
        $password = $this->request->getVar('password');

        $q = db('user')->where('username', $username)->get()->getRowArray();

        if (!$q) {
            gagal(base_url(), "User not found");
        }

        if (!password_verify($password, $q['password'])) {
            gagal(base_url(), "Password salah");
        }

        $data = [
            'id' => $q['id']
        ];

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
