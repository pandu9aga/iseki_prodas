<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check()) {
            return redirect()->route('login')->withErrors(['accessDenied' => 'You must login first']);
        }

        if (Auth::user()->Id_Type_User != 2) {
            Auth::logout(); // atau redirect ke halaman lain

            session()->forget('Id_User');
            session()->forget('Id_Type_User');
            session()->forget('Id_Area');
            session()->forget('Name_Area');
            
            return redirect()->route('login')->withErrors(['accessDenied' => 'Admin access required']);
        }

        return $next($request);
    }
}

