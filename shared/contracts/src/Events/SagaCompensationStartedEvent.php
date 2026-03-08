<?php

declare(strict_types=1);

namespace KV\Shared\Events;

use DateTimeImmutable;

/**
 * Raised by the saga orchestrator immediately before compensation begins.
 *
 * Listeners can use this event to:
 * - Trigger alerting / on-call escalation.
 * - Emit a distributed trace span for the compensation phase.
 * - Persist the pre-compensation state for post-mortem analysis.
 */
final class SagaCompensationStartedEvent
{
    /**
     * @param  string               $transactionId   UUID of the enclosing saga transaction.
     * @param  string               $failedStep      Name of the step that triggered compensation.
     * @param  string[]             $completedSteps  Ordered list of step names that completed
     *                                               successfully before the failure; these will
     *                                               be compensated in reverse order.
     * @param  array<string, mixed> $context         The saga context at the point compensation started.
     * @param  DateTimeImmutable    $startedAt       Timestamp when compensation was initiated.
     */
    public function __construct(
        public readonly string $transactionId,
        public readonly string $failedStep,
        public readonly array $completedSteps,
        public readonly array $context,
        public readonly DateTimeImmutable $startedAt,
    ) {}

    /**
     * Serialise to a plain array suitable for event payloads or logging.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'transaction_id'  => $this->transactionId,
            'failed_step'     => $this->failedStep,
            'completed_steps' => $this->completedSteps,
            'context'         => $this->context,
            'started_at'      => $this->startedAt->format(DateTimeImmutable::ATOM),
        ];
    }
}
