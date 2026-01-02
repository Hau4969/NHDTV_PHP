<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Movie extends Model
{
    use HasFactory;

    protected $table = 'movies';

        // app/Models/Movie.php
    protected $fillable = [
      'title','genre','duration','release_date',
      'director','cast','summary',
      'poster_url','poster_thumb','trailer_url',
      'status','is_now_showing',
    ];

    protected $casts = [
      'is_now_showing' => 'boolean',
      'release_date'   => 'date:Y-m-d',
    ];


    public function showtimes()
    {
        return $this->hasMany(Showtime::class, 'movie_id');
    }
    // app/Models/Movie.php
    public function scopeNowShowing($q){
        return $q->where('is_now_showing',1);
    }
    public function scopeUpcoming($q){
        return $q->where('is_now_showing',0)
                 ->orWhereDate('release_date','>',now());
    }

}
