<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BerkasSidangNol extends Model
{
    //
    use HasFactory;

    protected $table = 'berkas_sidang_nol';

    protected $fillable = [
        'user_id',
        'program_studi',
        'nomor_surat',
        'tanggal_dikirim',
        'tanggal_selesai',
        'dokumen_hasil_studi',
        'dokumen_data_diri',
        'dokumen_pddikti_ukt',
        'dokumen_ruangbaca_laboratorium_pkkmb_skpi',
        'dokumen_office_toefl',
        'dokumen_tambahan',
        'surat_balasan',
        'status',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
