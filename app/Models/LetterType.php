<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LetterType extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'category',
        'name',
        'notes_markdown',
        'approval_chain',
        'applicant_roles',
        'pdf_template',
    ];

    protected function casts(): array
    {
        return [
            'approval_chain' => 'array',
            'applicant_roles' => 'array',
        ];
    }

    public function letters(): HasMany
    {
        return $this->hasMany(Letter::class, 'type_id');
    }
}
