<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ThongTin extends Model
{
    use HasFactory;

    protected $table = 'thong_tin';
    protected $fillable = ['ten_rap', 'dia_chi', 'so_dien_thoai', 'email', 'gioi_thieu'];
}
