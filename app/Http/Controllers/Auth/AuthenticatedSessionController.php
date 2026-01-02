<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthenticatedSessionController extends Controller
{
    public function create()
    {
        return view('auth.login');
    }

// app/Http/Controllers/Auth/AuthenticatedSessionController.php

    public function store(Request $request)
    {
        $request->validate([
            'email' => ['required','email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($request->only('email','password'))) {
            $request->session()->regenerate();
            $user = Auth::user();

            // Admin → trang Quản lý phim
            if (optional($user->role)->name === 'Admin' || $user->role_id === 1) {
                // ưu tiên URL người dùng muốn vào trước (intended), fallback là movies.index
                return redirect()->intended(route('admin.movies.index'));
            }

            // User thường
            return redirect()->route('customer.home');
        }

        return back()->withErrors(['email' => 'Sai tài khoản hoặc mật khẩu.']);
    }

    public function destroy(Request $request)
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
    
        return redirect()->route('login'); // hoặc redirect('/login')
    }
    

}
