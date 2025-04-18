<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TemplateSurat extends Model
{
    use HasFactory;

    protected $table = 'template_surat';

    protected $fillable = [
        'jenis_surat_id',
        'nama',
        'deskripsi',
        'status',
        'dokumen_path',
        'tanggal_publish',
    ];

    public function jenisSurat()
    {
        return $this->belongsTo(JenisSurat::class);
    }
}
