<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Letter extends Model
{
    use HasFactory, HasUuids;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'type_id',
        'applicant_id',
        'applicant_name',
        'applicant_id_number',
        'program_studi',
        'category',
        'purpose',
        'status',
        'current_stage',
        'letter_number',
        'issued_at',
        'notes_snapshot',
        'meta',
        'final_pdf_path',
        'submitted_at',
        'archived_at',
        'pdf_error',
        'pdf_error_meta',
    ];

    protected function casts(): array
    {
        return [
            'issued_at' => 'date',
            'meta' => 'array',
            'submitted_at' => 'datetime',
            'archived_at' => 'datetime',
            'pdf_error' => 'boolean',
            'pdf_error_meta' => 'array',
        ];
    }

    public function letterType(): BelongsTo
    {
        return $this->belongsTo(LetterType::class, 'type_id');
    }

    public function applicant(): BelongsTo
    {
        return $this->belongsTo(User::class, 'applicant_id');
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(LetterAttachment::class);
    }

    public function routes(): HasMany
    {
        return $this->hasMany(LetterRoute::class);
    }

    public function logs(): HasMany
    {
        return $this->hasMany(LetterLog::class);
    }
}
