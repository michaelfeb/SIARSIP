<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Note extends Model
{
    use HasFactory;

    protected $fillable = [
        'berkas_persuratan_id',
        'user_id',
        'pesan',
    ];

    // Relasi ke BerkasPersuratan
    public function berkasPersuratan()
    {
        return $this->belongsTo(BerkasPersuratan::class);
    }

    // Relasi ke User
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
