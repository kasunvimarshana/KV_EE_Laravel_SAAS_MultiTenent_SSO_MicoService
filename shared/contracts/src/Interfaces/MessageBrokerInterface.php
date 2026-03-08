<?php

declare(strict_types=1);

namespace KV\Shared\Interfaces;

/**
 * Pluggable message broker abstraction.
 *
 * Implementations may back this contract with RabbitMQ, AWS SQS,
 * Apache Kafka, Redis Streams, or any other transport layer, allowing
 * services to remain decoupled from the underlying broker technology.
 */
interface MessageBrokerInterface
{
    /**
     * Publish a single message to the given topic / exchange / queue.
     *
     * @param  string $topic   Destination topic, exchange name, or queue name.
     * @param  array  $message The message payload (will be serialised by the implementation).
     * @param  array  $options Driver-specific options (e.g. routing key, headers, delay).
     * @return bool            True when the broker confirmed receipt.
     */
    public function publish(string $topic, array $message, array $options = []): bool;

    /**
     * Register a consumer callback for the given topic.
     *
     * The callback receives the raw broker message as its first argument.
     * The implementation is responsible for deserialising the payload before
     * invoking $callback.
     *
     * @param  string   $topic    Source topic, exchange name, or queue name.
     * @param  callable $callback Handler invoked for each received message.
     *                            Signature: function(mixed $message): void
     * @param  array    $options  Driver-specific options (e.g. prefetch count, consumer tag).
     * @return void
     */
    public function subscribe(string $topic, callable $callback, array $options = []): void;

    /**
     * Publish multiple messages to the given topic in a single batch operation.
     *
     * Implementations should use broker-native batch APIs when available
     * to reduce round-trip overhead.
     *
     * @param  string  $topic    Destination topic, exchange name, or queue name.
     * @param  array[] $messages List of message payloads.
     * @param  array   $options  Driver-specific options applied to every message in the batch.
     * @return bool              True when all messages were confirmed by the broker.
     */
    public function publishBatch(string $topic, array $messages, array $options = []): bool;

    /**
     * Positively acknowledge a message, signalling successful processing.
     *
     * After acknowledgement the broker will remove the message from the queue
     * and will not re-deliver it.
     *
     * @param  mixed $message The raw broker message object / envelope returned by subscribe().
     * @return void
     */
    public function acknowledge(mixed $message): void;

    /**
     * Negatively acknowledge a message, signalling failed processing.
     *
     * @param  mixed $message The raw broker message object / envelope returned by subscribe().
     * @param  bool  $requeue When true the broker re-queues the message for re-delivery;
     *                        when false the message is discarded or dead-lettered.
     * @return void
     */
    public function reject(mixed $message, bool $requeue = false): void;
}
