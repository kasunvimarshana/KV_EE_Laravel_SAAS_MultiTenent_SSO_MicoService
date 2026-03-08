<?php

declare(strict_types=1);

namespace App\Domain\Inventory\Entities;

use App\Domain\Inventory\Events\StockReserved;
use App\Domain\Inventory\Events\StockReleased;
use App\Domain\Inventory\Exceptions\InsufficientStockException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * Product domain entity — represents a product in the tenant's inventory.
 *
 * Columns:
 *   id, tenant_id, category_id, name, sku, description,
 *   price (decimal 12,4), cost (decimal 12,4),
 *   quantity (int), reserved_quantity (int), reorder_point (int),
 *   attributes (JSON), is_active (bool), metadata (JSON),
 *   created_by, updated_by, deleted_at, timestamps
 */
class Product extends Model
{
    use SoftDeletes, HasFactory;

    protected $table = 'products';

    protected $fillable = [
        'tenant_id',
        'category_id',
        'name',
        'sku',
        'description',
        'price',
        'cost',
        'quantity',
        'reserved_quantity',
        'reorder_point',
        'attributes',
        'is_active',
        'metadata',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'price'             => 'decimal:4',
        'cost'              => 'decimal:4',
        'quantity'          => 'integer',
        'reserved_quantity' => 'integer',
        'reorder_point'     => 'integer',
        'attributes'        => 'array',
        'metadata'          => 'array',
        'is_active'         => 'boolean',
        'deleted_at'        => 'datetime',
    ];

    // -------------------------------------------------------------------------
    // Domain behaviour
    // -------------------------------------------------------------------------

    /**
     * Returns units available for new orders (quantity minus already reserved).
     */
    public function getAvailableQuantity(): int
    {
        return max(0, $this->quantity - $this->reserved_quantity);
    }

    /**
     * Returns true when available stock has dropped to or below the reorder point.
     */
    public function isLowStock(): bool
    {
        return $this->getAvailableQuantity() <= $this->reorder_point;
    }

    /**
     * Move $qty units from available into reserved bucket.
     *
     * @throws InsufficientStockException
     */
    public function reserve(int $qty): void
    {
        if ($qty <= 0) {
            throw new \InvalidArgumentException('Reserve quantity must be positive.');
        }

        if ($this->getAvailableQuantity() < $qty) {
            throw new InsufficientStockException(
                "Cannot reserve {$qty} units of product #{$this->id}: " .
                "only {$this->getAvailableQuantity()} available."
            );
        }

        $this->reserved_quantity += $qty;

        event(new StockReserved($this, $qty));
    }

    /**
     * Return $qty reserved units back to available.
     */
    public function release(int $qty): void
    {
        if ($qty <= 0) {
            throw new \InvalidArgumentException('Release quantity must be positive.');
        }

        $this->reserved_quantity = max(0, $this->reserved_quantity - $qty);

        event(new StockReleased($this, $qty));
    }

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    public function category(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(ProductCategory::class, 'category_id');
    }

    public function stockMovements(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(StockMovement::class, 'product_id');
    }

    // -------------------------------------------------------------------------
    // Query scopes
    // -------------------------------------------------------------------------

    /** Filter by tenant. */
    public function scopeForTenant(\Illuminate\Database\Eloquent\Builder $query, string $tenantId): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('tenant_id', $tenantId);
    }

    /** Only active products. */
    public function scopeActive(\Illuminate\Database\Eloquent\Builder $query): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('is_active', true);
    }

    /** Products whose stock is at or below reorder point. */
    public function scopeLowStock(\Illuminate\Database\Eloquent\Builder $query): \Illuminate\Database\Eloquent\Builder
    {
        return $query->whereRaw('(quantity - reserved_quantity) <= reorder_point');
    }
}
