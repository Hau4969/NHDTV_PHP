<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Room extends Model
{
    use HasFactory;

    protected $table = 'rooms';

    protected $fillable = ['name','capacity'];

    public function showtimes()
    {
        return $this->hasMany(Showtime::class, 'room_id');
    }

    public function seats()
    {
        return $this->hasMany(Seat::class, 'room_id');
    }
}
