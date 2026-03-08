<?php

declare(strict_types=1);

namespace KV\Shared\DTOs;

use DateTimeImmutable;

/**
 * Represents an inventory reservation passed between microservices.
 *
 * Created by the Inventory service when stock is held for a pending order,
 * and consumed by the Order and Saga orchestration layers to track reservation
 * lifecycle (pending → confirmed → released / expired).
 */
final class InventoryDTO
{
    /**
     * @param  string                        $reservationId Unique reservation identifier (UUID v4).
     * @param  string                        $tenantId      UUID of the owning tenant.
     * @param  array<int, array{
     *             product_id: string,
     *             quantity: int
     *         }>                            $items         Products and quantities being reserved.
     * @param  string                        $status        Lifecycle status: 'pending' | 'confirmed' |
     *                                                      'released' | 'expired' | 'failed'.
     * @param  DateTimeImmutable|null        $expiresAt     When the reservation will be auto-released
     *                                                      if not confirmed; null for indefinite holds.
     * @param  array<string, mixed>          $metadata      Arbitrary key-value extension data.
     */
    public function __construct(
        public readonly string $reservationId,
        public readonly string $tenantId,
        public readonly array $items,
        public readonly string $status,
        public readonly ?DateTimeImmutable $expiresAt,
        public readonly array $metadata = [],
    ) {}

    /**
     * Hydrate from a plain associative array (e.g. decoded JSON event payload).
     *
     * @param  array<string, mixed> $data
     * @return static
     */
    public static function fromArray(array $data): static
    {
        $expiresAt = null;
        if (!empty($data['expires_at'])) {
            $expiresAt = new DateTimeImmutable((string) $data['expires_at']);
        }

        return new static(
            reservationId: (string) $data['reservation_id'],
            tenantId: (string) $data['tenant_id'],
            items: (array) ($data['items'] ?? []),
            status: (string) $data['status'],
            expiresAt: $expiresAt,
            metadata: (array) ($data['metadata'] ?? []),
        );
    }

    /**
     * Serialise to a plain associative array.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'reservation_id' => $this->reservationId,
            'tenant_id'      => $this->tenantId,
            'items'          => $this->items,
            'status'         => $this->status,
            'expires_at'     => $this->expiresAt?->format(DateTimeImmutable::ATOM),
            'metadata'       => $this->metadata,
        ];
    }

    /**
     * Determine whether this reservation is currently active and un-expired.
     */
    public function isActive(): bool
    {
        if ($this->status !== 'pending' && $this->status !== 'confirmed') {
            return false;
        }

        if ($this->expiresAt !== null && $this->expiresAt <= new DateTimeImmutable()) {
            return false;
        }

        return true;
    }

    /**
     * Return the total number of units reserved across all items.
     */
    public function totalQuantity(): int
    {
        return (int) array_sum(array_column($this->items, 'quantity'));
    }
}
