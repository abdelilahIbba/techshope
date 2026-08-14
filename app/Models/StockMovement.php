<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockMovement extends Model
{
    public const TYPE_SALE = 'sale';
    public const TYPE_RETURN = 'return';
    public const TYPE_EXCHANGE_IN = 'exchange_in';
    public const TYPE_EXCHANGE_OUT = 'exchange_out';
    public const TYPE_ADJUST = 'adjust';
    public const TYPE_INITIAL = 'initial';

    protected $fillable = [
        'product_id',
        'product_variant_id',
        'user_id',
        'type',
        'quantity_delta',
        'quantity_after',
        'reference_type',
        'reference_id',
        'notes',
    ];

    protected $casts = [
        'quantity_delta' => 'decimal:3',
        'quantity_after' => 'decimal:3',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
