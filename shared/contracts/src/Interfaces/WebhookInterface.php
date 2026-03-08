<?php

declare(strict_types=1);

namespace KV\Shared\Interfaces;

/**
 * Webhook dispatch and registration contract.
 *
 * Provides a unified API for outbound webhook delivery across all
 * microservices.  Each tenant can register multiple endpoint URLs and
 * subscribe to a subset of the platform's event types.
 */
interface WebhookInterface
{
    /**
     * Dispatch a webhook event to all registered endpoints for the tenant
     * that have subscribed to $event.
     *
     * Implementations should deliver webhooks asynchronously (e.g. via a
     * background job) and retry on transient failures with exponential back-off.
     *
     * @param  string $event    Dot-notation event name, e.g. "order.created".
     * @param  array  $payload  The event payload that will be sent in the request body.
     * @param  string $tenantId UUID of the tenant whose webhooks should be dispatched.
     * @return bool             True when the delivery was successfully enqueued.
     */
    public function dispatch(string $event, array $payload, string $tenantId): bool;

    /**
     * Register a new webhook endpoint for a tenant.
     *
     * @param  string   $url      HTTPS URL that will receive POST requests.
     * @param  string[] $events   List of event names the endpoint subscribes to,
     *                            e.g. ["order.created", "payment.completed"].
     *                            Pass ["*"] to subscribe to all events.
     * @param  string   $tenantId UUID of the owning tenant.
     * @return string             Opaque webhook ID that can be used to deregister later.
     */
    public function register(string $url, array $events, string $tenantId): string;

    /**
     * Remove a previously registered webhook endpoint.
     *
     * @param  string $webhookId The ID returned by {@see register()}.
     * @return bool              True on success; false when the ID was not found.
     */
    public function deregister(string $webhookId): bool;
}
