<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{
    /**
     * Trang chỉnh sửa hồ sơ (khớp route name: profile.edit)
     */
    public function edit()
    {
        $user = Auth::user();
        // View form chỉnh sửa (resources/views/profile/edit.blade.php)
        return view('profile.edit', compact('user'));
    }

    /**
     * Trang hiển thị hồ sơ đẹp như mockup (khớp route name: profile.view)
     */
    public function profileUser()
    {
        $user = Auth::user();
        // View “THÔNG TIN” (resources/views/profile/profile.blade.php)
        return view('profile.profile', compact('user'));
    }

    /**
     * Cập nhật hồ sơ (khớp route name: profile.update, method PATCH)
     */
    public function update(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'name'     => ['required','string','max:255'],
            'email'    => ['required','email','max:255', Rule::unique('users','email')->ignore($user->id)],
            'phone'    => ['nullable','regex:/^[0-9]{8,15}$/', Rule::unique('users','phone')->ignore($user->id)],
            'address'  => ['nullable','string','max:255'],
            'birthday' => ['nullable','date','before:tomorrow'],
            'avatar'   => ['nullable','image','mimes:jpg,jpeg,png,webp','max:2048'], // 2MB
        ], [
            'phone.regex' => 'Số điện thoại chỉ gồm 8–15 chữ số.',
            'birthday.before' => 'Ngày sinh không hợp lệ.',
        ]);

        // Upload avatar nếu có
        if ($request->hasFile('avatar')) {
            // xóa file cũ nếu có
            if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
                Storage::disk('public')->delete($user->avatar);
            }
            $path = $request->file('avatar')->store('avatars', 'public'); // storage/app/public/avatars
            $validated['avatar'] = $path;
        }

        $user->fill($validated)->save();

        return back()->with('success', 'Cập nhật hồ sơ thành công.');
    }

    /**
     * Xóa tài khoản (tuỳ dự án có dùng hay không)
     */
    public function destroy(Request $request)
    {
        $user = Auth::user();

        // Xoá avatar nếu có
        if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
            Storage::disk('public')->delete($user->avatar);
        }

        Auth::logout();

        // Nếu dùng tokens (Sanctum…) có thể revoke tại đây
        // $user->tokens()->delete();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/')->with('ok', 'Tài khoản đã được xoá.');
    }
}
