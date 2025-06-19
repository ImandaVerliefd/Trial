<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckUserSession
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next, ...$level)
    {
        if (!$request->session()->exists('user')) {
            return redirect('')->with('resp_msg', 'Sesi anda tidak ditemukan, silahkan untuk masuk kembali.');
        } else {
            if ((in_array(session('user')[0]['id_role'], $level))) {
                return $next($request);
            } else {
                return redirect('');
            }
        }
    }
}
