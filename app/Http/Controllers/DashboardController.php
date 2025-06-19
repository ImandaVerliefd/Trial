<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $data['title'] = "Dashboard";
        $data['content_page'] = 'layout/layout_admin/dashboard/index';
        $data['script'] = 'layout/layout_admin/dashboard/_html_script';
        return view('templates/main', $data);
    }
}
