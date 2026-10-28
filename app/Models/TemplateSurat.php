<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TemplateSurat extends Model
{
    use HasFactory;

    protected $table = 'template_surat';

    protected $fillable = [
        'nama',
        'deskripsi',
        'status',
        'dokumen_path',
        'tanggal_publish',
    ];

    protected function casts(): array
    {
        return ['status' => 'boolean', 'tanggal_publish' => 'date:Y-m-d'];
    }

}
