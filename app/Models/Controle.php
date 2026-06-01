<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Controle extends Model
{
    use HasFactory;

    protected $fillable = [
        'vehicule_id',
        'agent_id',
        'lieu',
        'place',
        'heure',
        'avenue',
        'date',
        'point_controle',
        'conditions_meteo',
        'observations',
        'latitude',
        'longitude',
    ];

    protected $casts = [
        'date' => 'date',
        'heure' => 'datetime:H:i',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
    ];

    /**
     * Relations UML.
     */
    public function vehicule(): BelongsTo
    {
        return $this->belongsTo(Vehicule::class);
    }

    public function agent(): BelongsTo
    {
        return $this->belongsTo(Agent::class);
    }

    /**
     * Controle génère 1..* Contraventions.
     */
    public function contraventions(): HasMany
    {
        return $this->hasMany(Contravention::class);
    }

    /**
     * UML methods.
     */
    public function rapporterResultat(): void
    {
        // Impl to log/report
        $this->update(['status' => 'reported']); // add status column if needed
    }

    public function cloturerControle(): void
    {
        $this->update(['status' => 'closed']);
    }
}
