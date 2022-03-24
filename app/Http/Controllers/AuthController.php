<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);
        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            return response()->json([
                'name' => Auth::user()->email,
            ], 200);
        }
        return response()->json([
            'status' => 500,
            'message' => 'user login failed'
        ], 500);
    }
}
