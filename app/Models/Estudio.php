<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Estudio extends Model
{
    protected $table = 'estudios';
    protected $fillable = ['appointment_id', 'nombre_estudio', 'archivo_pdf', 'observaciones'];

    public function appointment()
    {
        return $this->belongsTo(Appointment::class, 'appointment_id');
    }
}#Se cambio "return $table por return $this"