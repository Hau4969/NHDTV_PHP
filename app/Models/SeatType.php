<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SeatType extends Model
{
    use HasFactory;

    protected $table = 'seat_types';

    protected $fillable = ['name','base_price'];

    public function seats()
    {
        return $this->hasMany(Seat::class, 'seat_type_id');
    }
}
