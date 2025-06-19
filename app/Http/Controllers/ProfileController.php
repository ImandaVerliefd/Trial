<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function index()
    {
        $data['title'] = "Profil";
        $data['content_page'] = 'layout/layout_admin/profil/index';
        return view('templates/main', $data);
    }
}
