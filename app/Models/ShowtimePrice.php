<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ShowtimePrice extends Model
{
    use HasFactory;

    protected $table = 'showtime_prices';

    protected $fillable = [
        'showtime_id',
        'seat_type_id',
        'base_price',     // Giá gốc theo loại ghế
        'price',          // Giá điều chỉnh thủ công
        'final_price',    // Giá áp dụng thực tế (có thể = price hoặc base_price)
        'note',           // Ghi chú (VD: Ngày lễ +20%)
        'updated_by',     // ID người chỉnh sửa
    ];

    // ========== Quan hệ ==========
    public function showtime()
    {
        return $this->belongsTo(Showtime::class, 'showtime_id');
    }

    public function seatType()
    {
        return $this->belongsTo(SeatType::class, 'seat_type_id');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
