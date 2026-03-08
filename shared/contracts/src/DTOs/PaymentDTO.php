<?php

declare(strict_types=1);

namespace KV\Shared\DTOs;

/**
 * Represents a payment record passed between microservices.
 *
 * Shared between the Order, Payment, and Notification services.
 * Contains everything needed to describe a payment attempt and its outcome
 * without coupling consumers to the Payment service's internal model.
 */
final class PaymentDTO
{
    /**
     * @param  string      $paymentId            Unique payment identifier (UUID v4).
     * @param  string      $orderId              UUID of the associated order.
     * @param  string      $tenantId             UUID of the owning tenant.
     * @param  float       $amount               Charge amount in the major unit (e.g. 19.99).
     * @param  string      $currency             ISO 4217 currency code, e.g. "USD", "EUR".
     * @param  string      $method               Payment method: 'card' | 'bank_transfer' | 'wallet' | etc.
     * @param  string      $status               Payment status: 'pending' | 'authorised' | 'captured' |
     *                                           'refunded' | 'failed' | 'cancelled'.
     * @param  string|null $transactionReference Reference returned by the payment gateway (may be null
     *                                           until the gateway responds).
     * @param  array<string, mixed> $metadata    Arbitrary key-value extension data.
     */
    public function __construct(
        public readonly string $paymentId,
        public readonly string $orderId,
        public readonly string $tenantId,
        public readonly float $amount,
        public readonly string $currency,
        public readonly string $method,
        public readonly string $status,
        public readonly ?string $transactionReference,
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
            paymentId: (string) $data['payment_id'],
            orderId: (string) $data['order_id'],
            tenantId: (string) $data['tenant_id'],
            amount: (float) $data['amount'],
            currency: (string) $data['currency'],
            method: (string) $data['method'],
            status: (string) $data['status'],
            transactionReference: isset($data['transaction_reference'])
                ? (string) $data['transaction_reference']
                : null,
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
            'payment_id'            => $this->paymentId,
            'order_id'              => $this->orderId,
            'tenant_id'             => $this->tenantId,
            'amount'                => $this->amount,
            'currency'              => $this->currency,
            'method'                => $this->method,
            'status'                => $this->status,
            'transaction_reference' => $this->transactionReference,
            'metadata'              => $this->metadata,
        ];
    }

    /**
     * Whether the payment reached a terminal successful state.
     */
    public function isCaptured(): bool
    {
        return $this->status === 'captured';
    }

    /**
     * Whether the payment ended in a failure or cancellation.
     */
    public function isTerminalFailure(): bool
    {
        return in_array($this->status, ['failed', 'cancelled'], true);
    }
}
