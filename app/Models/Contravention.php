<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Controle;
use App\Models\BaremePrix;

class Contravention extends Model
{
    use HasFactory;

    protected $table = 'contraventions';

    protected $fillable = [
        'vehicule_id',
        'agent_id',
        'type',
        'montant',
        'lieu',
        'latitude',
        'longitude',
        'description',
        'statut',
        'code_unique',
        'est_payee',
        'reference_paiement',
        'paiement_fournisseur',
        'paiement_transaction_id',
        'paiement_statut',
        'date_infraction',
        'documents_manquants',
    ];

    protected $casts = [
        'date_infraction' => 'datetime',
        'est_payee' => 'boolean',
        'montant' => 'decimal:2',
        'latitude' => 'float',
        'longitude' => 'float',
        'documents_manquants' => 'array',
    ];

    public function vehicule(): BelongsTo
    {
        return $this->belongsTo(Vehicule::class, 'vehicule_id');
    }

    public function agent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'agent_id');
    }

    public function controle(): BelongsTo
    {
        return $this->belongsTo(Controle::class);
    }

    public function baremePrix(): BelongsTo
    {
        return $this->belongsTo(BaremePrix::class);
    }

    /**
     * UML methods.
     */
    public function enregisterParAgent(Agent $agent): self
    {
        $this->agent()->associate($agent->user);
        return $this;
    }

    // Méthodes diagramme
    public function lierAuVehicule(Vehicule $vehicule): self
    {
        return $this->setRelation('vehicule', $vehicule);
    }

    public function statutImmatriculation()
    {
        // Logic based on vehicule documents
        return $this->vehicule->documents()->where('date_expiration', '<', now())->count() > 0 ? 'non_conforme' : 'conforme';
    }
}

