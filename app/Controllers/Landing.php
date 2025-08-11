<?php

namespace App\Controllers;

class Landing extends BaseController
{
    public function index(): string
    {

        if (session('id')) {
            sukses(base_url("home"), "You are logged");
        }
        return view('guest/landing', ['judul' => "9Clean"]);
    }
}
