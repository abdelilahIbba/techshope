<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\StockMovement;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class VariantStockService
{
    public function generateUniqueBarcode(): string
    {
        do {
            $barcode = (string) random_int(100000000000, 999999999999);
        } while (
            ProductVariant::query()->where('barcode', $barcode)->exists()
            || Product::query()->where('barcode', $barcode)->exists()
        );

        return $barcode;
    }

    /**
     * Build the cartesian product of sizes × colors and create missing variants.
     * Empty sizes/colors yields a single default variant.
     *
     * @param  list<string>  $sizes
     * @param  list<string>  $colors
     * @return list<ProductVariant>
     */
    public function syncVariants(
        Product $product,
        array $sizes,
        array $colors,
        float $stockPerVariant = 0,
        ?string $baseBarcode = null,
    ): array {
        $sizes = $this->normalizeList($sizes);
        $colors = $this->normalizeList($colors);

        $combinations = [];
        if ($sizes === [] && $colors === []) {
            $combinations[] = ['size' => null, 'color' => null, 'is_default' => true];
        } else {
            $sizeList = $sizes === [] ? [null] : $sizes;
            $colorList = $colors === [] ? [null] : $colors;
            foreach ($sizeList as $size) {
                foreach ($colorList as $color) {
                    $combinations[] = [
                        'size' => $size,
                        'color' => $color,
                        'is_default' => false,
                    ];
                }
            }
        }

        $createdOrExisting = [];
        $isFirst = true;

        foreach ($combinations as $combo) {
            $existing = ProductVariant::query()
                ->where('product_id', $product->id)
                ->where('size', $combo['size'])
                ->where('color', $combo['color'])
                ->first();

            if ($existing) {
                $createdOrExisting[] = $existing;
                $isFirst = false;
                continue;
            }

            $barcode = ($isFirst && $baseBarcode)
                ? $baseBarcode
                : $this->generateUniqueBarcode();

            // Avoid colliding with an existing variant barcode.
            if (ProductVariant::query()->where('barcode', $barcode)->exists()) {
                $barcode = $this->generateUniqueBarcode();
            }

            $variant = ProductVariant::query()->create([
                'product_id' => $product->id,
                'size' => $combo['size'],
                'color' => $combo['color'],
                'barcode' => $barcode,
                'stock_quantity' => $stockPerVariant,
                'is_default' => (bool) $combo['is_default'],
            ]);

            if ($stockPerVariant > 0) {
                $this->recordMovement(
                    $variant,
                    StockMovement::TYPE_INITIAL,
                    $stockPerVariant,
                    Product::class,
                    $product->id,
                    'Stock initial variante'
                );
            }

            $createdOrExisting[] = $variant;
            $isFirst = false;
        }

        $this->syncProductAggregateStock($product);

        return $createdOrExisting;
    }

    /**
     * Ensure the product has at least one default variant (legacy / simple articles).
     */
    public function ensureDefaultVariant(Product $product, ?float $initialStock = null): ProductVariant
    {
        $variant = $product->variants()->where('is_default', true)->first()
            ?? $product->variants()->first();

        if ($variant) {
            return $variant;
        }

        $stock = $initialStock ?? (float) $product->stock_quantity;

        $variant = ProductVariant::query()->create([
            'product_id' => $product->id,
            'size' => null,
            'color' => null,
            'barcode' => $product->barcode ?: $this->generateUniqueBarcode(),
            'stock_quantity' => $stock,
            'is_default' => true,
        ]);

        $this->syncProductAggregateStock($product);

        return $variant;
    }

    public function adjust(ProductVariant $variant, string $type, float $quantity, ?string $reason = null): ProductVariant
    {
        $current = (float) $variant->stock_quantity;
        $newStock = match ($type) {
            'add' => $current + $quantity,
            'remove' => max(0, $current - $quantity),
            'set' => $quantity,
            default => $current,
        };

        $delta = $newStock - $current;
        $variant->stock_quantity = $newStock;
        $variant->save();

        $this->recordMovement(
            $variant,
            StockMovement::TYPE_ADJUST,
            $delta,
            null,
            null,
            $reason
        );

        $this->syncProductAggregateStock($variant->product);

        return $variant->fresh();
    }

    public function decrement(ProductVariant $variant, float $quantity, string $type, ?string $referenceType = null, ?int $referenceId = null, ?string $notes = null): void
    {
        if ($quantity <= 0) {
            return;
        }

        if ((float) $variant->stock_quantity < $quantity) {
            abort(422, 'Stock insuffisant pour: '.$variant->displayName());
        }

        $variant->stock_quantity = (float) $variant->stock_quantity - $quantity;
        $variant->save();

        $this->recordMovement($variant, $type, -$quantity, $referenceType, $referenceId, $notes);
        $this->syncProductAggregateStock($variant->product);
    }

    public function increment(ProductVariant $variant, float $quantity, string $type, ?string $referenceType = null, ?int $referenceId = null, ?string $notes = null): void
    {
        if ($quantity <= 0) {
            return;
        }

        $variant->stock_quantity = (float) $variant->stock_quantity + $quantity;
        $variant->save();

        $this->recordMovement($variant, $type, $quantity, $referenceType, $referenceId, $notes);
        $this->syncProductAggregateStock($variant->product);
    }

    public function syncProductAggregateStock(?Product $product): void
    {
        if (! $product) {
            return;
        }

        $total = (float) $product->variants()->sum('stock_quantity');
        $product->forceFill(['stock_quantity' => $total])->saveQuietly();
    }

    public function findByBarcode(string $barcode): ?ProductVariant
    {
        return ProductVariant::query()
            ->with('product')
            ->where('barcode', $barcode)
            ->first();
    }

    /**
     * @param  list<string|null>  $values
     * @return list<string>
     */
    private function normalizeList(array $values): array
    {
        $normalized = [];
        foreach ($values as $value) {
            if ($value === null) {
                continue;
            }
            foreach (preg_split('/[,;|]+/', (string) $value) ?: [] as $part) {
                $part = trim($part);
                if ($part !== '') {
                    $normalized[] = $part;
                }
            }
        }

        return array_values(array_unique($normalized));
    }

    private function recordMovement(
        ProductVariant $variant,
        string $type,
        float $delta,
        ?string $referenceType,
        ?int $referenceId,
        ?string $notes,
    ): void {
        StockMovement::query()->create([
            'product_id' => $variant->product_id,
            'product_variant_id' => $variant->id,
            'user_id' => Auth::id(),
            'type' => $type,
            'quantity_delta' => $delta,
            'quantity_after' => (float) $variant->stock_quantity,
            'reference_type' => $referenceType,
            'reference_id' => $referenceId,
            'notes' => $notes ? Str::limit($notes, 255) : null,
        ]);
    }
}
