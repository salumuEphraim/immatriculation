<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Proprietaire extends Model
{
    use HasFactory;

    /**
     * Les attributs qui peuvent être assignés massivement.
     * Ajout de 'user_id' pour permettre la connexion au compte utilisateur.
     */
    protected $fillable = [
        'user_id', // Indispensable pour lier le profil au compte de connexion
        'nom', 
        'postnom', 
        'prenom', 
        'email', 
        'telephone', 
        'adresse', 
        'commune',
        'quartier',
        'numero_identite',
        'sexe',
        'date_naissance',
        'lieu_naissance',
        'nationalite',
        'profession',
    ];

    protected $casts = [
        'date_naissance' => 'date',
    ];

    /**
     * Relation avec le compte Utilisateur (Connexion).
     * Permet au propriétaire d'avoir un email et un mot de passe pour se connecter.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relation avec les véhicules (One-to-Many).
     * Un propriétaire peut posséder plusieurs véhicules enregistrés.
     */
    public function vehicules(): HasMany
    {
        // On précise la clé étrangère pour éviter toute confusion avec l'ancien user_id
        return $this->hasMany(Vehicule::class, 'proprietaire_id');
    }

    /**
     * Accessor pour obtenir le nom complet.
     * Pratique pour afficher "Ephraïm Salumu Kizenga" dans tes vues Blade.
     */
    public function getNomCompletAttribute(): string
    {
        return trim("{$this->prenom} {$this->nom} {$this->postnom}");
    }
}
