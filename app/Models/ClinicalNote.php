<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClinicalNote extends Model
{
    /**
     * Lista explícita: ningún request del usuario se asigna en masa a este modelo,
     * pero así queda protegido si algún día alguien escribe ->update($request->all()).
     */
    protected $fillable = [
        'appointment_id',
        'user_id',
        'employee_id',
        'title',
        'status',
        'audio_path',
        'context',
        'transcription',
        'organized_content',
        'txt_path',
        'docx_path',
        'pdf_path',
        'error_message',
        'transcribed_at',
        'organized_at',
        'confirmed_at',
    ];

    protected $casts = [
        'context' => 'encrypted:array',
        'transcription' => 'encrypted',
        'organized_content' => 'encrypted',
        'transcribed_at' => 'datetime',
        'organized_at' => 'datetime',
        'confirmed_at' => 'datetime',
    ];

    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}
