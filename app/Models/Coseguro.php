<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Coseguro extends Model
{
    protected $table = 'coseguros';

    protected $fillable = [
        'obra_social_id',
        'service_id',
        'monto',
        'activo',
    ];

    public function obraSocial()
    {
        return $this->belongsTo(ObraSocial::class, 'obra_social_id');
    }

    public function service()
    {
        return $this->belongsTo(Service::class, 'service_id');
    }
}
