<?php

declare(strict_types=1);

namespace KV\Shared\Interfaces;

/**
 * Contract for individual health-check probes.
 *
 * Each service dependency (database, cache, message broker, external API, etc.)
 * should expose a dedicated implementation of this interface.  An aggregator
 * can collect all registered probes and compose a single /health endpoint
 * response from their results.
 *
 * Example response shape from check():
 * ```php
 * [
 *     'status'  => 'healthy',
 *     'details' => [
 *         'latency_ms' => 2,
 *         'connected'  => true,
 *     ],
 * ]
 * ```
 */
interface HealthCheckInterface
{
    /**
     * Execute the health probe and return its result.
     *
     * Implementations must not throw exceptions; any error should be captured
     * and reflected in the returned status and details.
     *
     * @return array{
     *     status: 'healthy'|'degraded'|'unhealthy',
     *     details: array<string, mixed>
     * }
     */
    public function check(): array;

    /**
     * Return a unique, human-readable name for this probe.
     *
     * Used as the key when aggregating multiple checks.
     *
     * @return string e.g. "database", "redis", "rabbitmq", "stripe_api".
     */
    public function getName(): string;
}
