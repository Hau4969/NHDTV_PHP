<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Carbon\Carbon;

class UsersController extends Controller
{
    public function index(Request $r)
    {
        $q = $r->query('q');

        $users = User::query()
            ->when($q, function ($qq) use ($q) {
                $qq->where(function ($w) use ($q) {
                    $w->where('name', 'like', "%{$q}%")
                      ->orWhere('email', 'like', "%{$q}%")
                      ->orWhere('phone', 'like', "%{$q}%");
                });
            })
            ->orderByDesc('created_at')
            ->paginate(15)
            ->withQueryString();

        return view('admin.users.index', compact('users', 'q'));
    }

    public function create()
    {
        return view('admin.users.form', ['user' => new User()]);
    }

    public function store(Request $r)
    {
        $data = $r->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'email', 'max:255', 'unique:users,email'],
            'phone'    => ['nullable', 'regex:/^[0-9]{8,15}$/', 'unique:users,phone'],
            'password' => ['required', 'string', 'min:6'],
            // 1=Admin, 2=Customer
            'role_id'  => ['required', Rule::in([1, 2])],
            'birthday' => ['nullable', 'date'],
            'address'  => ['nullable', 'string', 'max:255'],
        ]);

        $data['password'] = bcrypt($data['password']);

        User::create($data);
        return redirect()->route('admin.users.index')->with('ok', 'Tạo tài khoản thành công.');
    }

    public function edit(User $user)
    {
        if ($user->id === auth()->id()) {
            return redirect()->route('admin.users.index')
                ->withErrors('Bạn không thể chỉnh sửa tài khoản của chính mình từ trang quản trị.');
        }
        return view('admin.users.form', compact('user'));
    }

    public function update(Request $r, User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->withErrors('Bạn không thể chỉnh sửa tài khoản của chính mình.');
        }

        $data = $r->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'phone'    => ['nullable', 'regex:/^[0-9]{8,15}$/', Rule::unique('users', 'phone')->ignore($user->id)],
            'role_id'  => ['required', Rule::in([1, 2])],
            'password' => ['nullable', 'string', 'min:6'],
            'birthday' => ['nullable', 'date'],
            'address'  => ['nullable', 'string', 'max:255'],
        ]);

        if (!empty($data['password'])) {
            $data['password'] = bcrypt($data['password']);
        } else {
            unset($data['password']);
        }

        $user->update($data);
        return redirect()->route('admin.users.index')->with('ok', 'Cập nhật thành công.');
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->withErrors('Bạn không thể xoá tài khoản đang đăng nhập.');
        }
        $user->delete();
        return back()->with('ok', 'Đã xoá tài khoản.');
    }

    public function resetPassword(Request $request, User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->withErrors('Bạn không thể reset mật khẩu của chính mình.');
        }

        // mặc định = ddmmyyyy nếu có birthday, nếu không thì random
        $plain = null;
        if (!empty($user->birthday)) {
            try {
                $plain = Carbon::parse($user->birthday)->format('dmY');
            } catch (\Throwable $e) {
                // ignore
            }
        }
        if (!$plain) {
            $plain = Str::upper(Str::random(8));
        }

        $user->password = Hash::make($plain);
        // Nếu có cột must_change_password thì bật:
        // $user->must_change_password = true;
        $user->save();

        // Không hiển thị plaintext ra giao diện
        return back()->with('ok', "Đã reset mật khẩu cho {$user->name}.");
    }
}
