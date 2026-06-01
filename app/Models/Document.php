<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Document extends Model
{
    use HasFactory;

    protected $fillable = [
        'vehicule_id',
        'type',
        'date_emission',
        'date_expiration',
        'numero_plaque',
        'serie',
        'centre_perception',
        'data',
    ];

    protected $casts = [
        'date_emission' => 'date',
        'date_expiration' => 'date',
        'data' => 'array',
    ];

    public function vehicule(): BelongsTo
    {
        return $this->belongsTo(Vehicule::class);
    }

    // Méthodes du diagramme
    public function valider(): void
    {
        $this->update(['statut' => 'valide']); // add statut if needed
    }

    public function expirer(): void
    {
        $this->update(['statut' => 'expiree']);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function getDaysRemainingAttribute(): int
    {
        if (!$this->date_expiration) {
            return 0;
        }

        return now()->diffInDays($this->date_expiration, false);
    }

    public function getExpirationStatusAttribute(): string
    {
        if (!$this->date_expiration) {
            return 'inconnu';
        }

        if ($this->date_expiration->isPast()) {
            return 'expired';
        }

        if ($this->days_remaining <= 7) {
            return 'urgent';
        }

        if ($this->days_remaining <= 30) {
            return 'warning';
        }

        return 'valid';
    }
}

