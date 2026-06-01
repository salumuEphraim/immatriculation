<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Plaque extends Model
{
    use HasFactory;

    protected $table = 'plaques';

    protected $fillable = [
        'vehicule_id',
        'numero_plaque',
        'type',
        'date_delivrance',
        'date_expiration',
        'serie_plaque',
        'centre_perception',
    ];

    protected $casts = [
        'date_delivrance' => 'date',
        'date_expiration' => 'date',
    ];

    public function vehicule(): BelongsTo
    {
        return $this->belongsTo(Vehicule::class);
    }
}

