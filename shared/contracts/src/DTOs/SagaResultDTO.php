<?php

declare(strict_types=1);

namespace KV\Shared\DTOs;

use DateTimeImmutable;

/**
 * Represents the final outcome of a distributed saga transaction.
 *
 * Returned by {@see \KV\Shared\Interfaces\SagaOrchestratorInterface::execute()}
 * and may be persisted, published to an event stream, or returned from an API
 * controller as-is.
 */
final class SagaResultDTO
{
    /**
     * @param  string               $transactionId  Unique saga run identifier (UUID v4).
     * @param  string               $status         Overall outcome: 'completed' | 'compensated' | 'failed'.
     * @param  array<string, mixed> $steps          Per-step execution records keyed by step name.
     *                                              Each entry: ['status' => '...', 'duration_ms' => int, 'error' => ?string]
     * @param  array<string, mixed> $context        Final accumulated saga context.
     * @param  array<string, mixed> $errors         Errors keyed by step name.
     * @param  DateTimeImmutable    $startedAt      When the saga began executing.
     * @param  DateTimeImmutable|null $completedAt  When the saga finished (null while still running).
     */
    public function __construct(
        public readonly string $transactionId,
        public readonly string $status,
        public readonly array $steps,
        public readonly array $context,
        public readonly array $errors,
        public readonly DateTimeImmutable $startedAt,
        public readonly ?DateTimeImmutable $completedAt,
    ) {}

    /**
     * Determine whether the saga completed successfully.
     */
    public function isSuccessful(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * Determine whether compensation was triggered.
     */
    public function wasCompensated(): bool
    {
        return $this->status === 'compensated';
    }

    /**
     * Determine whether the saga ended in a non-compensatable failure.
     */
    public function hasFailed(): bool
    {
        return $this->status === 'failed';
    }

    /**
     * Return elapsed time in milliseconds (microsecond precision), or null
     * when the saga is still running.
     *
     * Uses microseconds from the DateTimeImmutable format string to avoid
     * losing sub-second precision that would occur with integer timestamps alone.
     */
    public function elapsedMs(): ?int
    {
        if ($this->completedAt === null) {
            return null;
        }

        $startUs = (int) $this->startedAt->format('Uu');    // seconds + microseconds as one integer
        $endUs   = (int) $this->completedAt->format('Uu');

        return (int) round(($endUs - $startUs) / 1000);
    }

    /**
     * Serialise to a plain array suitable for JSON encoding or event payloads.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'transaction_id' => $this->transactionId,
            'status'         => $this->status,
            'steps'          => $this->steps,
            'context'        => $this->context,
            'errors'         => $this->errors,
            'started_at'     => $this->startedAt->format(DateTimeImmutable::ATOM),
            'completed_at'   => $this->completedAt?->format(DateTimeImmutable::ATOM),
            'elapsed_ms'     => $this->elapsedMs(),
        ];
    }
}
