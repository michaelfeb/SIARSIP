<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Carousel extends Model
{
    use HasFactory;

    protected $table = 'carousels';

    protected $fillable = [
        'nama',
        'gambar',
        'tanggal_publish',
        'status',
    ];

    protected $casts = [
        'tanggal_publish' => 'date',
    ];
}
