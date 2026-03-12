<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    protected $fillable = [
        'category_id',
        'supplier_id',
        'name',
        'barcode',
        'barcode_type',
        'description',
        'purchase_price',
        'sale_price',
        'stock_quantity',
        'unit',
        'sell_by_length',
        'length_unit',
    ];

    protected $casts = [
        'sell_by_length'  => 'boolean',
        'stock_quantity'  => 'decimal:3',
        'purchase_price'  => 'decimal:2',
        'sale_price'      => 'decimal:2',
    ];

    public function saleItems(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }
}
