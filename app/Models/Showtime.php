<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Showtime extends Model
{
    use HasFactory;

    protected $table = 'showtimes';

    protected $fillable = [
        'movie_id',
        'room_id',
        'start_time',
        'end_time',
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time'   => 'datetime',
    ];

    // Quan hệ bắt buộc để with(['movie','room']) hoạt động
    public function movie()
    {
        return $this->belongsTo(Movie::class, 'movie_id');
    }

    public function room()
    {
        return $this->belongsTo(Room::class, 'room_id');
    }

    // Nếu có bảng giá theo suất & loại ghế
    public function prices()
    {
        return $this->hasMany(ShowtimePrice::class, 'showtime_id');
    }

    // Nếu có vé
    public function tickets()
    {
        return $this->hasMany(Ticket::class, 'showtime_id');
    }
}
