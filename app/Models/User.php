<?php

namespace App\Models;

use App\Models\Role;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;

class User extends Authenticatable
{
    use Notifiable;

    protected $fillable = [
        'role_id',   // <— thêm dòng này
        'name',
        'email',
        'phone',
        'password',
        'address',
        'birthday',
        'avatar',
    ];


    


    // 🔒 Tự động mã hóa mật khẩu khi set
    public function setPasswordAttribute($value)
    {
        if (!empty($value)) {
            // Nếu password chưa mã hóa thì mã hóa
            $this->attributes['password'] = Hash::needsRehash($value)
                ? Hash::make($value)
                : $value;
        }
    }

    // Nếu có liên kết Role thì thêm:
    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function tickets()
    {
        return $this->hasMany(\App\Models\Ticket::class, 'user_id');
    }
    
}
