<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JenisSurat extends Model
{
    use HasFactory;

    protected $table = 'jenis_surat';

    protected $fillable = [
        'nama',
        'status',
    ];

    public function templateSurat()
    {
        return $this->hasMany(TemplateSurat::class, 'jenis_surat_id');
    }

    public function berkasPersuratan()
    {
        return $this->hasMany(BerkasPersuratan::class, 'jenis_surat_id');
    }
}
