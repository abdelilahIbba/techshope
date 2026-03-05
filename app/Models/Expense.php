<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Expense extends Model
{
    protected $fillable = [
        'created_by_user_id',
        'approved_by_user_id',
        'category',
        'description',
        'amount',
        'expense_date',
        'payment_method',
        'status',
        'notes',
        'receipt_path',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'expense_date' => 'date',
        ];
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by_user_id');
    }

    public const CATEGORIES = [
        'rent' => 'Loyer',
        'utilities' => 'Utilités (électricité, eau, internet)',
        'salaries' => 'Salaires',
        'supplies' => 'Fournitures',
        'maintenance' => 'Maintenance',
        'marketing' => 'Marketing',
        'other' => 'Autre',
    ];

    public const STATUSES = [
        'pending' => 'En attente',
        'approved' => 'Approuvée',
        'rejected' => 'Rejetée',
        'paid' => 'Payée',
    ];

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeByCategory($query, string $category)
    {
        return $query->where('category', $category);
    }
}
