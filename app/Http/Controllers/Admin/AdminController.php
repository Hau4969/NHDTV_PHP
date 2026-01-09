<?php

namespace App\Http\Controllers\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\Controller;

class AdminController extends Controller
{
    public function editProfile()
    {
        return view('profile.edit', ['user' => Auth::user()]);
    }

    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'name'     => ['required','string','max:255'],
            'email'    => ['required','email','max:255', Rule::unique('users','email')->ignore($user->id)],
            'phone'    => ['nullable','regex:/^[0-9]{8,15}$/', Rule::unique('users','phone')->ignore($user->id)],
            'address'  => ['nullable','string','max:255'],
            'birthday' => ['nullable','date'],
            'avatar'   => ['nullable','image','mimes:jpg,jpeg,png,webp','max:2048'],
        ]);

        // xử lý ảnh đại diện
        if ($request->hasFile('avatar')) {
            if ($user->avatar) {
                Storage::disk('public')->delete($user->avatar);
            }
            $path = $request->file('avatar')->store('avatars', 'public'); // storage/app/public/avatars
            $user->avatar = $path;
        }

        $user->fill($request->only('name','email','phone','address','birthday'))->save();

        return back()->with('success', 'Cập nhật thông tin thành công!');
    }
}
