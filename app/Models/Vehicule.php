<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Vehicule extends Model
{
    use HasFactory;

    protected $fillable = [
        'plaque_immatriculation',
        'vin',
        'marque',
        'modele',
        'couleur',
        'proprietaire_id',
        'statut_reglementaire',
    ];

    protected $casts = [];

    public const REQUIRED_DOCUMENT_TYPES = [
        'assurance' => 'Assurance',
        'vignette' => 'Vignette',
        'controle_technique' => 'Controle technique',
        'carte_rose' => 'Carte rose',
    ];

    public const EXTERNAL_DOCUMENT_TYPES = [
        'permis_conduire' => 'Permis de conduire',
        'plaque' => 'Plaque / immatriculation',
        'immatriculation' => 'Certificat d immatriculation',
    ];

    public const DOCUMENT_TYPES = self::REQUIRED_DOCUMENT_TYPES + self::EXTERNAL_DOCUMENT_TYPES;

    public const STATUTS_REGLEMENTAIRES = [
        'en_regle' => 'En regle',
        'pas_en_regle' => 'Pas en regle',
    ];

    public function getPlaqueAttribute(): ?string
    {
        return $this->plaque_immatriculation;
    }

    public function getEnRegleAttribute(): bool
    {
        return $this->isEnRegle();
    }

    public function getHasAssuranceAttribute(): bool
    {
        return $this->hasDocumentType('assurance');
    }

    public function getHasVignetteAttribute(): bool
    {
        return $this->hasDocumentType('vignette');
    }

    public function getHasControleTechniqueAttribute(): bool
    {
        return $this->hasDocumentType('controle_technique');
    }

    public function getHasCarteRoseAttribute(): bool
    {
        return $this->hasDocumentType('carte_rose');
    }

    public function hasDocumentType(string $type): bool
    {
        $documents = $this->relationLoaded('documents') ? $this->documents : $this->documents()->get();

        return $documents->contains(function (Document $document) use ($type) {
            return $document->type === $type;
        });
    }

    public function isEnRegle(): bool
    {
        return $this->statut_reglementaire === 'en_regle';
    }

    public static function computeStatutFromDocuments(array $documents): string
    {
        $selected = collect($documents)
            ->filter(fn ($type) => array_key_exists($type, self::REQUIRED_DOCUMENT_TYPES))
            ->unique();

        return $selected->count() === count(self::REQUIRED_DOCUMENT_TYPES)
            ? 'en_regle'
            : 'pas_en_regle';
    }

    public function appartientA(User $user): bool
    {
        return (bool) ($this->proprietaire && $this->proprietaire->user_id === $user->id);
    }

    public function proprietaire(): BelongsTo
    {
        return $this->belongsTo(Proprietaire::class, 'proprietaire_id');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(Document::class);
    }

    public function contraventions(): HasMany
    {
        return $this->hasMany(Contravention::class);
    }

    public function plaques(): HasMany
    {
        return $this->hasMany(Plaque::class);
    }

    public function controles(): HasMany
    {
        return $this->hasMany(Controle::class);
    }
}
