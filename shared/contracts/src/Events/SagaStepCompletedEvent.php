<?php

declare(strict_types=1);

namespace KV\Shared\Events;

use DateTimeImmutable;

/**
 * Raised by the saga orchestrator after a step executes successfully.
 *
 * Consumers (e.g. audit loggers, tracing middleware) may listen to this
 * event to record fine-grained saga progress without coupling to the
 * orchestrator implementation.
 */
final class SagaStepCompletedEvent
{
    /**
     * @param  string               $transactionId  UUID of the enclosing saga transaction.
     * @param  string               $stepName       The {@see \KV\Shared\Interfaces\SagaStepInterface::getName()} value.
     * @param  array<string, mixed> $context        The accumulated saga context after this step ran.
     * @param  DateTimeImmutable    $completedAt    Timestamp of successful step completion.
     */
    public function __construct(
        public readonly string $transactionId,
        public readonly string $stepName,
        public readonly array $context,
        public readonly DateTimeImmutable $completedAt,
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
            'context'        => $this->context,
            'completed_at'   => $this->completedAt->format(DateTimeImmutable::ATOM),
        ];
    }
}
