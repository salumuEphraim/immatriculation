<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Infraction extends Model
{
    use HasFactory;

    /**
     * Les attributs qui peuvent être assignés en masse.
     * Ajout de code_unique et est_payee pour correspondre aux contrôleurs.
     */
    protected $fillable = [
        'vehicule_id',
        'agent_id',
        'type',
        'montant',
        'lieu',
        'latitude',
        'longitude',
        'description',
        'statut', // 'en_attente', 'validee', 'rejetee'
        'code_unique', // Indispensable pour le reçu RS-XXXX
        'est_payee', // Boolean pour le suivi financier
        'reference_paiement',
        'paiement_fournisseur',
        'paiement_transaction_id',
        'paiement_statut',
        'date_infraction',
    ];

    /**
     * Cast des attributs pour garantir le bon type de données.
     */
    protected $casts = [
        'date_infraction' => 'datetime',
        'est_payee' => 'boolean',
        'montant' => 'decimal:2',
        'latitude' => 'float',
        'longitude' => 'float',
    ];

    /**
     * Récupérer le véhicule associé à cette infraction.
     */
    public function vehicule(): BelongsTo
    {
        return $this->belongsTo(Vehicule::class, 'vehicule_id');
    }

    /**
     * Récupérer l'agent (User) qui a signalé l'infraction.
     */
    public function agent(): BelongsTo
    {
        // On précise 'agent_id' car c'est le nom de la clé étrangère dans votre table
        return $this->belongsTo(User::class, 'agent_id');
    }
}