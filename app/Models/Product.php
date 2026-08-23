<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Product extends Model
{
    protected $fillable = [
        'category_id',
        'supplier_id',
        'brand_id',
        'name',
        'barcode',
        'barcode_type',
        'description',
        'image',
        'purchase_price',
        'sale_price',
        'stock_quantity',
        'unit',
        'sell_by_length',
        'length_unit',
    ];

    protected $casts = [
        'sell_by_length' => 'boolean',
        'stock_quantity' => 'decimal:3',
        'purchase_price' => 'decimal:2',
        'sale_price' => 'decimal:2',
    ];

    protected $appends = [
        'has_variants',
        'available_sizes',
        'available_colors',
        'image_url',
    ];

    /**
     * The `image` column holds either a storage-relative path (uploaded
     * file) or a full external URL (provided by URL) — resolve whichever it
     * is into a browser-ready URL.
     */
    public function getImageUrlAttribute(): ?string
    {
        if (! $this->image) {
            return null;
        }

        if (str_starts_with($this->image, 'http://') || str_starts_with($this->image, 'https://')) {
            return $this->image;
        }

        return Storage::disk('public')->url($this->image);
    }

    public function saleItems(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function getHasVariantsAttribute(): bool
    {
        if ($this->relationLoaded('variants')) {
            return $this->variants->contains(fn (ProductVariant $v) => ! $v->is_default);
        }

        return $this->variants()->where('is_default', false)->exists();
    }

    public function getAvailableSizesAttribute(): array
    {
        $source = $this->relationLoaded('variants')
            ? $this->variants
            : $this->variants()->get();

        return $source->pluck('size')->filter()->unique()->values()->all();
    }

    public function getAvailableColorsAttribute(): array
    {
        $source = $this->relationLoaded('variants')
            ? $this->variants
            : $this->variants()->get();

        return $source->pluck('color')->filter()->unique()->values()->all();
    }
}
