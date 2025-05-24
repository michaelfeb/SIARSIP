<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BerkasPersuratan extends Model
{
    //
    use HasFactory;

    protected $table = 'berkas_persuratan';

    protected $fillable = [
        'user_id',
        'jenis_surat_id',
        'nomor_surat',
        'keterangan',
        'berkas_mahasiswa',
        'berkas_balasan',
        'berkas_tambahan',
        'status',
        'tanggal_dikirim',
        'program_studi',
    ];

    protected $casts = [
        'berkas_mahasiswa' => 'array',
        'berkas_balasan' => 'array',
        'berkas_tambahan' => 'array',
        'tanggal_dikirim' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function jenisSurat()
    {
        return $this->belongsTo(JenisSurat::class);
    }

    public function notes()
    {
        return $this->hasMany(Note::class, 'berkas_id')->where('jenis_berkas', 1);
    }
}
