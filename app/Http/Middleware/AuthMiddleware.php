<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; // Tidak digunakan lagi di sini
use Symfony\Component\HttpFoundation\Response;

class AuthMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        // Periksa apakah session Id_User atau Id_Area ada
        if (! session()->has('Id_User') && ! session()->has('Id_Area')) {
            // Jika tidak ada keduanya, arahkan ke login
            return redirect()->route('login')->withErrors(['accessDenied' => 'You must login first']);
        }

        // Lanjutkan ke request berikutnya
        return $next($request);
    }
}