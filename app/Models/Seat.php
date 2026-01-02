<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Seat extends Model
{
    use HasFactory;

    protected $table = 'seats';

    // Tuỳ schema bạn đã chọn: row_letter/seat_number hoặc label
    protected $fillable = [
        'room_id',
        'row_letter',   // hoặc 'label'
        'seat_number',  // nếu dùng cặp hàng/cột
        'seat_type_id',
    ];

    public function room()
    {
        return $this->belongsTo(Room::class, 'room_id');
    }

    public function seatType()
    {
        return $this->belongsTo(SeatType::class, 'seat_type_id');
    }
}
