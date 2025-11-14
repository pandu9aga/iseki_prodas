<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    // public function handle(Request $request, Closure $next): Response
    // {
    //     if (!session()->has('Id_User')) {
    //         return redirect()->route('login')->withErrors(['accessDenied' => 'You must login first']);
    //     }

    //     if(session('Id_Type_User') != 2) {
    //         session()->forget('Id_User');
    //         session()->forget('Id_Type_User');
    //         return redirect()->route('login')->withErrors(['accessDenied' => 'You must login with admin account']);
    //     }

    //     return $next($request);
    // }

    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check()) {
            return redirect()->route('login')->withErrors(['accessDenied' => 'You must login first']);
        }

        if (Auth::user()->Id_Type_User != 2) {
            Auth::logout(); // atau redirect ke halaman lain

            session()->forget('Id_User');
            session()->forget('Id_Type_User');
            
            return redirect()->route('login')->withErrors(['accessDenied' => 'Admin access required']);
        }

        return $next($request);
    }
}

