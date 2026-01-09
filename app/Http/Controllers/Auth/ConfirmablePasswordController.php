<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ConfirmablePasswordController extends Controller
{
    public function show()
    {
        return view('auth.confirm-password');
    }

    public function store(Request $request)
    {
        $request->validate([
            'password' => ['required','current_password'],
        ]);

        $request->session()->put('auth.password_confirmed_at', time());

        return redirect()->intended();
    }
}
