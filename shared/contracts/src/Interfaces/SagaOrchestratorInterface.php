<?php

declare(strict_types=1);

namespace KV\Shared\Interfaces;

use KV\Shared\DTOs\SagaResultDTO;

/**
 * Saga orchestrator contract.
 *
 * The orchestrator drives a distributed saga by executing registered
 * {@see SagaStepInterface} steps in order and coordinating compensation
 * if any step fails.
 *
 * Usage pattern:
 * ```php
 * $result = $orchestrator
 *     ->addStep(new ReserveInventoryStep())
 *     ->addStep(new CreatePaymentIntentStep())
 *     ->addStep(new PlaceOrderStep())
 *     ->execute(['tenant_id' => $tenantId, 'order_data' => $orderData]);
 * ```
 */
interface SagaOrchestratorInterface
{
    /**
     * Register a step to be executed as part of this saga.
     *
     * Steps are executed in the order they are added.
     *
     * @param  SagaStepInterface $step The step to register.
     * @return static                  Fluent interface for chaining.
     */
    public function addStep(SagaStepInterface $step): static;

    /**
     * Run the saga with the provided initial context.
     *
     * The orchestrator executes each registered step in sequence, accumulating
     * context between steps. If a step throws an exception the orchestrator
     * triggers compensation for all previously completed steps (in reverse order)
     * and returns a {@see SagaResultDTO} with status 'compensated' or 'failed'.
     *
     * @param  array         $initialContext Seed data available to the first step.
     * @return SagaResultDTO Result of the saga execution.
     */
    public function execute(array $initialContext): SagaResultDTO;

    /**
     * Return the current status of the saga.
     *
     * @return string One of: 'pending', 'running', 'completed', 'compensating',
     *                        'compensated', 'failed'.
     */
    public function getStatus(): string;

    /**
     * Return the unique transaction identifier assigned to this saga run.
     *
     * The transaction ID is generated when the orchestrator is instantiated
     * and remains stable across retries.
     *
     * @return string UUID v4 string.
     */
    public function getTransactionId(): string;
}
