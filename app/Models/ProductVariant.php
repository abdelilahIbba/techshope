<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductVariant extends Model
{
    protected $fillable = [
        'product_id',
        'size',
        'color',
        'barcode',
        'stock_quantity',
        'is_default',
    ];

    protected $casts = [
        'stock_quantity' => 'decimal:3',
        'is_default' => 'boolean',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function saleItems(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    public function label(): string
    {
        $parts = array_filter([
            $this->size ? 'Taille '.$this->size : null,
            $this->color ? 'Couleur '.$this->color : null,
        ]);

        return $parts === [] ? 'Standard' : implode(' · ', $parts);
    }

    public function displayName(): string
    {
        $base = $this->product?->name ?? 'Produit';
        $brand = $this->product?->brand?->name;

        return trim(($brand ? $brand.' — ' : '').$base.' ('.$this->label().')');
    }
}
