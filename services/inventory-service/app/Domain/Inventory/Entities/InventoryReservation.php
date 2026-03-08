<?php

declare(strict_types=1);

namespace App\Domain\Inventory\Entities;

use Illuminate\Database\Eloquent\Model;

/**
 * InventoryReservation — tracks stock held for a Saga distributed transaction.
 *
 * Columns:
 *   id, tenant_id, reservation_id (UUID), order_id,
 *   status ENUM('pending','confirmed','released','expired'),
 *   items (JSON: [{'product_id':1,'quantity':2}]),
 *   expires_at (timestamp), metadata (JSON), timestamps
 */
class InventoryReservation extends Model
{
    public const STATUS_PENDING   = 'pending';
    public const STATUS_CONFIRMED = 'confirmed';
    public const STATUS_RELEASED  = 'released';
    public const STATUS_EXPIRED   = 'expired';

    protected $table = 'inventory_reservations';

    protected $fillable = [
        'tenant_id',
        'reservation_id',
        'order_id',
        'status',
        'items',
        'expires_at',
        'metadata',
    ];

    protected $casts = [
        'items'      => 'array',
        'metadata'   => 'array',
        'expires_at' => 'datetime',
    ];

    // -------------------------------------------------------------------------
    // Domain helpers
    // -------------------------------------------------------------------------

    public function isExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    // -------------------------------------------------------------------------
    // Scopes
    // -------------------------------------------------------------------------

    public function scopeForTenant(\Illuminate\Database\Eloquent\Builder $query, string $tenantId): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('tenant_id', $tenantId);
    }

    public function scopeExpired(\Illuminate\Database\Eloquent\Builder $query): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('status', self::STATUS_PENDING)
                     ->where('expires_at', '<', now());
    }
}
