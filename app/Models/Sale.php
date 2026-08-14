<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Sale extends Model
{
    protected $fillable = [
        'customer_id',
        'cashier_user_id',
        'date',
        'total_amount',
        'payment_method',
        'status',
        'notes',
    ];

    protected $casts = [
        'date' => 'datetime',
        'total_amount' => 'decimal:2',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }

    public function receipt(): HasOne
    {
        return $this->hasOne(\App\Models\Receipt::class);
    }

    public function cashier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cashier_user_id');
    }

    public function cancellation(): HasOne
    {
        return $this->hasOne(SaleCancellation::class);
    }

    public function exchanges(): HasMany
    {
        return $this->hasMany(SaleExchange::class);
    }

    // Scopes utiles
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeCancelled($query)
    {
        return $query->where('status', 'cancelled');
    }

    public function scopeByCashier($query, int $userId)
    {
        return $query->where('cashier_user_id', $userId);
    }

    public function scopeDateRange($query, string $from, string $to)
    {
        return $query->whereBetween('date', [$from . ' 00:00:00', $to . ' 23:59:59']);
    }
}
