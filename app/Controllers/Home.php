<?php

namespace App\Controllers;

class Home extends BaseController
{
    public function index()
    {
        return view('pages/dashboard', [
            'title' => 'Dashboard',
        ]);
    }
}