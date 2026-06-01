<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Models\Vehicule;
use App\Models\Proprietaire;
use App\Models\Contravention; // Importation du modèle Contravention
use App\Models\Agent;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'nom',        
        'prenom',     
        'niuu',       
        'telephone',  
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */

    /**
     * Un utilisateur peut avoir un profil Propriétaire (Citoyen).
     */
    public function proprietaire()
    {
        return $this->hasOne(Proprietaire::class, 'user_id');
    }

    /**
     * Accès direct aux véhicules via le profil propriétaire (Has Many Through).
     */
    public function vehicules()
    {
        return $this->hasManyThrough(
            Vehicule::class, 
            Proprietaire::class, 
            'user_id',         
            'proprietaire_id', 
            'id',              
            'id'               
        );
    }

    /**
     * Relation pour l'historique des infractions signalées par l'agent.
     * C'est ce qui permet de faire $agent->infractions.
     */
    public function contraventions()
    {
        // On précise 'agent_id' car c'est la clé étrangère dans la table infractions
        return $this->hasMany(Contravention::class, 'agent_id');
    }

    /*
    |--------------------------------------------------------------------------
    | Fonctions d'aide pour les Rôles
    |--------------------------------------------------------------------------
    */

    public function hasRole($role): bool
    {
        return $this->role === $role;
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isAgent(): bool
    {
        return $this->role === 'agent';
    }

    public function isProprietaire(): bool
    {
        return $this->role === 'proprietaire';
    }

    /**
     * User has one Agent profile if role='agent'.
     */
    public function agent()
    {
        return $this->hasOne(Agent::class, 'user_id');
    }
}
