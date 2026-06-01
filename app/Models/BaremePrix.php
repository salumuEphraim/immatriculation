<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BaremePrix extends Model
{
    use HasFactory;

    protected $table = 'bareme_prix';

    protected $fillable = [
        'code_infraction',
        'libelle',
        'montant_base',
        'majoration_retard',
        'delai_paiement',
    ];

    protected $casts = [
        'montant_base' => 'decimal:2',
        'majoration_retard' => 'decimal:2',
    ];

    /**
     * BaremePrix concerne 1..* Contravention.
     */
    public function contraventions(): HasMany
    {
        return $this->hasMany(Contravention::class);
    }

    /**
     * UML methods.
     */
    public function calculerMontantTotal(int $jours_retard): float
    {
        $majoration = $jours_retard > $this->delai_paiement ? $this->majoration_retard * $jours_retard : 0;
        return $this->montant_base + $majoration;
    }

    public static function getInfractionParType(string $type): ?self
    {
        return self::where('code_infraction', $type)->first();
    }
}
