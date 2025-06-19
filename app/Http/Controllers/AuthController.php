<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use App\Models\UMSApi;


class AuthController extends Controller
{
    public function index()
    {
        $data['title'] = "Masuk";
        $data['content_page'] = 'auth/login';
        return
            view('auth.login', $data);
    }

    public function AuthenticateLogin(Request $req)
    {
        $email = strip_tags($req->input('email'));
        $pass = $req->input('password');

        $DataJWT = UMSApi::Login($email, $pass);
        if ($DataJWT['status_code'] !== 200) {
            return redirect('')->with('resp_msg', $DataJWT['status_message']);
        }

        if (empty(session('jwt'))) {
            Session::push('jwt', collect([
                'data' => $DataJWT['data']['jwt']
            ]));
        } else {
            Session::put('jwt', collect([
                ['data' => $DataJWT['data']['jwt']]
            ]));
        }

        $ActiveSession = UMSApi::CheckActiveSession();
        if ($ActiveSession['status_code'] !== 200) {
            Session::forget('user');
            return redirect('')->with('resp_msg', $ActiveSession['status_message']);
        }

        $account = $ActiveSession['data'];
        if (empty(session('user'))) {
            $user_data = collect([
                'email' => $account['EMAIL'],
                'id_role' => $account['ID_ROLE'],
                'id_user' => $account['ID_USER'],
                'kode_user' => $account['KODE'],
                'foto' => (!empty($account['FOTO']) ? $account['FOTO'] : ""),
                'nrp' => $account['NIM'],
                'nama' => $account['NAMA'],
                'prodi' => $account['PRODI']
            ]);
            Session::push('user', $user_data);
        }

        return redirect('dashboard');
    }

    public function logout()
    {
        Session::flush();
        return redirect('');
    }
}
