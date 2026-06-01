<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Agent extends Model
{
    use HasFactory;

    protected $fillable = [
        'nom',
        'email',
        'matricule',
        'user_id',
    ];

    /**
     * Relation with User.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Agent effectue 1..* Controles.
     */
    public function controles(): HasMany
    {
        return $this->hasMany(Controle::class);
    }

    /**
     * Agent enregistre 1..* Contraventions.
     */
    public function contraventions(): HasMany
    {
        return $this->hasMany(Contravention::class);
    }
}
