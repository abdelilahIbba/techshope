<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExternalOrder extends Model
{
    protected $fillable = [
        'client_name',
        'client_phone',
        'client_address',
        'platform_source',
        'status',
        'total_amount',
        'notes',
        'created_by_user_id',
    ];

    protected function casts(): array
    {
        return [
            'total_amount' => 'decimal:2',
        ];
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    // ── Constants ────────────────────────────────────────────────────────────

    public const PLATFORMS = ['Instagram', 'WhatsApp', 'Autre'];

    public const STATUSES = ['En attente', 'Expédié', 'Livré', 'Annulé'];

    public const STATUS_COLORS = [
        'En attente' => ['bg' => '#fef9c3', 'text' => '#854d0e', 'dot' => '#ca8a04'],   // yellow
        'Expédié'    => ['bg' => '#dbeafe', 'text' => '#1e40af', 'dot' => '#3b82f6'],   // blue
        'Livré'      => ['bg' => '#dcfce7', 'text' => '#166534', 'dot' => '#22c55e'],   // green
        'Annulé'     => ['bg' => '#fee2e2', 'text' => '#991b1b', 'dot' => '#ef4444'],   // red
    ];

    public const PLATFORM_COLORS = [
        'Instagram' => ['bg' => '#fce7f3', 'text' => '#9d174d'],
        'WhatsApp'  => ['bg' => '#dcfce7', 'text' => '#166534'],
        'Autre'     => ['bg' => '#f1f5f9', 'text' => '#475569'],
    ];

    // ── Scopes ───────────────────────────────────────────────────────────────

    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopeByPlatform($query, string $platform)
    {
        return $query->where('platform_source', $platform);
    }
}
