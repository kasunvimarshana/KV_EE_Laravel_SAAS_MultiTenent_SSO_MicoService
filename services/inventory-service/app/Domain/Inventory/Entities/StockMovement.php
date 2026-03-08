<?php

declare(strict_types=1);

namespace App\Domain\Inventory\Entities;

use Illuminate\Database\Eloquent\Model;

/**
 * StockMovement — immutable audit trail of every stock change.
 *
 * Columns:
 *   id, tenant_id, product_id,
 *   type ENUM('in','out','reserve','release','adjust'),
 *   quantity (int, can be negative for adjustments),
 *   reference_type (string, e.g. 'order'), reference_id,
 *   notes, metadata (JSON), created_by, created_at
 */
class StockMovement extends Model
{
    public const TYPE_IN      = 'in';
    public const TYPE_OUT     = 'out';
    public const TYPE_RESERVE = 'reserve';
    public const TYPE_RELEASE = 'release';
    public const TYPE_ADJUST  = 'adjust';

    public const UPDATED_AT = null; // stock movements are immutable

    protected $table = 'stock_movements';

    protected $fillable = [
        'tenant_id',
        'product_id',
        'type',
        'quantity',
        'reference_type',
        'reference_id',
        'notes',
        'metadata',
        'created_by',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'metadata' => 'array',
    ];

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    public function product(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    // -------------------------------------------------------------------------
    // Scopes
    // -------------------------------------------------------------------------

    public function scopeForTenant(\Illuminate\Database\Eloquent\Builder $query, string $tenantId): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('tenant_id', $tenantId);
    }

    public function scopeForProduct(\Illuminate\Database\Eloquent\Builder $query, string|int $productId): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('product_id', $productId);
    }
}
