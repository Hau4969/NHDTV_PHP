<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class RegisteredUserController extends Controller
{
    public function create()
    {
        return view('auth.register');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'     => ['required','string','max:255'],
            'email'    => ['required','email','max:255', 'unique:users,email'],
            'phone'    => ['nullable','regex:/^[0-9]{8,15}$/', Rule::unique('users','phone')],
            'password' => ['required', Password::min(8)->mixedCase()->numbers()->symbols(), 'confirmed'],
        ]);

        // role_id: 2 = Customer (hoặc truy vấn theo tên role nếu muốn an toàn)
        $customerRoleId = 2;

        $user = User::create([
            'role_id'  => $customerRoleId,
            'name'     => $request->name,
            'email'    => $request->email,
            'phone'    => $request->phone,
            'password' => Hash::make($request->password),
        ]);

        event(new Registered($user)); // kích hoạt gửi mail verify nếu bật

        Auth::login($user);

        // Nếu dùng xác thực email: chuyển tới prompt
        if (config('auth.verify_emails', true)) {
            return redirect()->route('verification.notice');
        }
        return redirect()->route('user.dashboard');
    }
}
