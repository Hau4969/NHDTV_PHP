<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    protected $table = 'roles'; // Tên bảng trong database

    protected $fillable = [
        'name',   // nếu cột trong bảng roles là 'name'
        // thêm các cột khác nếu có (vd: description)
    ];

    public $timestamps = false; // nếu bảng roles KHÔNG có created_at / updated_at
}
