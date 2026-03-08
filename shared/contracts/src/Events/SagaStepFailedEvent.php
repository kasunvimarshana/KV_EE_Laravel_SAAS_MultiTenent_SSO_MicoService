<?php

declare(strict_types=1);

namespace KV\Shared\Events;

use DateTimeImmutable;

/**
 * Raised by the saga orchestrator when a step throws an exception.
 *
 * Emitted before compensation begins.  Consumers such as alerting systems,
 * dead-letter monitors, or audit trails should subscribe to this event.
 */
final class SagaStepFailedEvent
{
    /**
     * @param  string               $transactionId  UUID of the enclosing saga transaction.
     * @param  string               $stepName       The {@see \KV\Shared\Interfaces\SagaStepInterface::getName()} value.
     * @param  string               $error          Human-readable error description (exception message).
     * @param  array<string, mixed> $context        The saga context at the point of failure.
     * @param  DateTimeImmutable    $failedAt       Timestamp when the failure was detected.
     */
    public function __construct(
        public readonly string $transactionId,
        public readonly string $stepName,
        public readonly string $error,
        public readonly array $context,
        public readonly DateTimeImmutable $failedAt,
    ) {}

    /**
     * Serialise to a plain array suitable for event payloads or logging.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'transaction_id' => $this->transactionId,
            'step_name'      => $this->stepName,
            'error'          => $this->error,
            'context'        => $this->context,
            'failed_at'      => $this->failedAt->format(DateTimeImmutable::ATOM),
        ];
    }
}
