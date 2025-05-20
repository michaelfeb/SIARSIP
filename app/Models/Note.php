<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Note extends Model
{
    use HasFactory;

    protected $fillable = [
        'berkas_id',
        'jenis_berkas',
        'user_id',
        'pesan',
    ];

    public function berkasPersuratan()
    {
        return $this->belongsTo(BerkasPersuratan::class, 'berkas_id')->where('jenis_berkas', 1);
    }

    public function berkasSidangNol()
    {
        return $this->belongsTo(BerkasSidangNol::class, 'berkas_id')->where('jenis_berkas', 2);
    }

    // Relasi ke User
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
