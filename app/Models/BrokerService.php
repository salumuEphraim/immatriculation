<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BrokerService extends Model
{
    use HasFactory;

    protected $table = 'brokers_services';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'endpoint',
        'api_key',
        'doc_types',
        'active',
        'timeout',
        'description',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'doc_types' => 'array',
        'active' => 'boolean',
    ];

    /**
     * Scope for active brokers.
     */
    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    /**
     * Check if broker handles a doc type.
     */
    public function handlesDocType(string $type): bool
    {
        return in_array($type, $this->doc_types ?? []);
    }
}
