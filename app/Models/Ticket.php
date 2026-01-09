<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Ticket extends Model
{
    use HasFactory;

    protected $table = 'tickets';

    protected $fillable = [
        'user_id',
        'voucher_id',
        'showtime_id',
        'seat_id',
        'qr_code',
        'final_price',
        'discount_amount',
        'membership_discount_rate',
        'status',
        'points_earned',
    ];

    protected $casts = [
        'final_price'              => 'decimal:2',
        'discount_amount'          => 'decimal:2',
        'membership_discount_rate' => 'decimal:2',
    ];

    public function user()     { return $this->belongsTo(User::class, 'user_id'); }
    public function voucher()  { return $this->belongsTo(Voucher::class, 'voucher_id'); }
    public function showtime() { return $this->belongsTo(Showtime::class, 'showtime_id'); }
    public function seat()     { return $this->belongsTo(Seat::class, 'seat_id'); }
}
