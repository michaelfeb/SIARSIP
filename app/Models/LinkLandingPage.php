<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LinkLandingPage extends Model
{
    //
    protected $table = 'link_landing_page';

    protected $fillable = [
        'nama',
        'icon',
        'color',
        'deskripsi',
        'no_urut',
        'status',
        'link',
    ];
}
