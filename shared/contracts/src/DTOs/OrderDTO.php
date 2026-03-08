<?php

declare(strict_types=1);

namespace KV\Shared\DTOs;

/**
 * Represents a customer order passed between microservices.
 *
 * This DTO is the canonical order representation shared by the Order,
 * Inventory, Payment, and Notification services.  It is intentionally
 * kept as a plain data carrier with no domain behaviour.
 */
final class OrderDTO
{
    /**
     * @param  string                                           $orderId      Unique order identifier (UUID v4).
     * @param  string                                           $tenantId     UUID of the owning tenant.
     * @param  string                                           $customerId   UUID of the customer who placed the order.
     * @param  array<int, array{
     *             product_id: string,
     *             quantity: int,
     *             price: float
     *         }>                                               $items        Line items in the order.
     * @param  float                                            $totalAmount  Grand total in the tenant's base currency.
     * @param  string                                           $status       Order lifecycle status, e.g. 'pending' | 'confirmed' |
     *                                                                        'processing' | 'shipped' | 'delivered' | 'cancelled'.
     * @param  array<string, mixed>                             $metadata     Arbitrary key-value pairs for extensibility.
     */
    public function __construct(
        public readonly string $orderId,
        public readonly string $tenantId,
        public readonly string $customerId,
        public readonly array $items,
        public readonly float $totalAmount,
        public readonly string $status,
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
        return new static(
            orderId: (string) $data['order_id'],
            tenantId: (string) $data['tenant_id'],
            customerId: (string) $data['customer_id'],
            items: (array) ($data['items'] ?? []),
            totalAmount: (float) $data['total_amount'],
            status: (string) $data['status'],
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
            'order_id'     => $this->orderId,
            'tenant_id'    => $this->tenantId,
            'customer_id'  => $this->customerId,
            'items'        => $this->items,
            'total_amount' => $this->totalAmount,
            'status'       => $this->status,
            'metadata'     => $this->metadata,
        ];
    }

    /**
     * Return the number of distinct line items in the order.
     */
    public function itemCount(): int
    {
        return count($this->items);
    }

    /**
     * Return the sum of all item quantities.
     */
    public function totalQuantity(): int
    {
        return (int) array_sum(array_column($this->items, 'quantity'));
    }
}
