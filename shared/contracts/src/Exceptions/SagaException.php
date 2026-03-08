<?php

declare(strict_types=1);

namespace KV\Shared\Exceptions;

use RuntimeException;
use Throwable;

/**
 * Thrown by the saga orchestrator or saga steps when an unrecoverable
 * error occurs during execution or compensation.
 *
 * Carries the saga transaction ID so that callers can correlate the
 * exception with the saga's audit trail without having to pass the ID
 * through every catch block explicitly.
 */
class SagaException extends RuntimeException
{
    /**
     * @param  string         $transactionId  UUID of the saga transaction that failed.
     * @param  string         $message        Human-readable error description.
     * @param  int            $code           Optional application error code.
     * @param  Throwable|null $previous       The underlying cause, if any.
     */
    public function __construct(
        private readonly string $transactionId,
        string $message = '',
        int $code = 0,
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, $code, $previous);
    }

    /**
     * Return the UUID of the saga transaction associated with this exception.
     */
    public function getTransactionId(): string
    {
        return $this->transactionId;
    }

    /**
     * Create a SagaException from an existing Throwable, preserving the
     * original exception as the cause.
     *
     * @param  string    $transactionId UUID of the failing saga transaction.
     * @param  Throwable $previous      The underlying exception.
     * @return static
     */
    public static function fromThrowable(string $transactionId, Throwable $previous): static
    {
        return new static(
            transactionId: $transactionId,
            message: $previous->getMessage(),
            code: (int) $previous->getCode(),
            previous: $previous,
        );
    }
}
