<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SaleCancellation extends Model
{
    protected $fillable = [
        'sale_id',
        'cancelled_by_user_id',
        'authorized_by_user_id',
        'reason',
        'notes',
        'refund_amount',
        'refund_method',
        'cancelled_at',
    ];

    protected function casts(): array
    {
        return [
            'refund_amount' => 'decimal:2',
            'cancelled_at' => 'datetime',
        ];
    }

    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    public function cancelledBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cancelled_by_user_id');
    }

    public function authorizedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'authorized_by_user_id');
    }

    public const REASONS = [
        'client_request' => 'Demande client',
        'error' => 'Erreur de saisie',
        'fraud' => 'Suspicion de fraude',
        'return' => 'Retour marchandise',
        'other' => 'Autre raison',
    ];
}
