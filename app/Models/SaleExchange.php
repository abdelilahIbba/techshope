<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SaleExchange extends Model
{
    protected $fillable = [
        'sale_id',
        'original_sale_item_id',
        'returned_variant_id',
        'exchanged_variant_id',
        'processed_by_user_id',
        'quantity',
        'price_difference',
        'reason',
        'notes',
    ];

    protected $casts = [
        'quantity' => 'decimal:3',
        'price_difference' => 'decimal:2',
    ];

    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    public function originalItem(): BelongsTo
    {
        return $this->belongsTo(SaleItem::class, 'original_sale_item_id');
    }

    public function returnedVariant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'returned_variant_id');
    }

    public function exchangedVariant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'exchanged_variant_id');
    }

    public function processedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by_user_id');
    }
}
