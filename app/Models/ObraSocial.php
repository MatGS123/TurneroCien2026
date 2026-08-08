<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ObraSocial extends Model
{
    protected $table = 'obras_sociales';

    protected $fillable = [
        'nombre',
        'plan',
        'prestadora',
        'activo',
    ];

    public $timestamps = true;

    public function coseguros()
    {
        return $this->hasMany(Coseguro::class, 'obra_social_id');
    }

    public function appointments()
    {
        return $this->hasMany(Appointment::class, 'obra_social_id');
    }
}
