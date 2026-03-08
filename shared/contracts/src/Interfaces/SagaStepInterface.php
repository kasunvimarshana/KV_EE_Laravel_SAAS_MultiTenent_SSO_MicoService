<?php

declare(strict_types=1);

namespace KV\Shared\Interfaces;

/**
 * A single step (participant) in a distributed saga transaction.
 *
 * Each step encapsulates one local transaction and its corresponding
 * compensating transaction, following the Saga pattern for managing
 * distributed data consistency without two-phase commit.
 *
 * Steps are executed sequentially by a {@see SagaOrchestratorInterface}.
 * If any step fails, the orchestrator calls compensate() on all previously
 * completed steps in reverse order.
 */
interface SagaStepInterface
{
    /**
     * Execute the step's local transaction.
     *
     * Implementations must be idempotent: if the saga is retried after a
     * transient failure the step may be called more than once with the same
     * context.
     *
     * @param  array $context Shared saga context carrying data produced by
     *                        previously executed steps.
     * @return array          Updated context that will be passed to the next step.
     *
     * @throws \Throwable Any exception signals step failure and triggers compensation.
     */
    public function execute(array $context): array;

    /**
     * Execute the compensating (rollback) transaction for this step.
     *
     * Called when a subsequent step has failed and the orchestrator is
     * unwinding completed steps in reverse order.
     *
     * @param  array $context The shared saga context at the point of failure.
     * @return array          Updated context (e.g. with compensation receipt data).
     */
    public function compensate(array $context): array;

    /**
     * Return the human-readable name of this step.
     *
     * Used for logging, tracing, and event payloads.
     *
     * @return string e.g. "reserve_inventory", "create_payment_intent".
     */
    public function getName(): string;

    /**
     * Return the maximum number of seconds this step is allowed to run
     * before the orchestrator considers it timed out.
     *
     * @return int Positive integer; typical values are 5–30 seconds.
     */
    public function getTimeout(): int;
}
