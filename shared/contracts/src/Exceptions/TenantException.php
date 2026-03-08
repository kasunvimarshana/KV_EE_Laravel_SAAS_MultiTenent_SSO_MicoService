<?php

declare(strict_types=1);

namespace KV\Shared\Exceptions;

use RuntimeException;
use Throwable;

/**
 * Thrown when tenant resolution, isolation, or configuration fails.
 *
 * Examples of situations that should raise this exception:
 * - The incoming request does not carry a recognisable tenant identifier.
 * - The resolved tenant is suspended or does not exist.
 * - A service attempts to access another tenant's data.
 */
class TenantException extends RuntimeException
{
    /**
     * @param  string         $tenantId  The tenant UUID that was involved in the failure
     *                                   (may be an empty string when the tenant could not
     *                                   be resolved at all).
     * @param  string         $message   Human-readable error description.
     * @param  int            $code      Optional application error code.
     * @param  Throwable|null $previous  The underlying cause, if any.
     */
    public function __construct(
        private readonly string $tenantId,
        string $message = '',
        int $code = 0,
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, $code, $previous);
    }

    /**
     * Return the tenant UUID associated with this exception.
     *
     * An empty string indicates the tenant could not be resolved from the request.
     */
    public function getTenantId(): string
    {
        return $this->tenantId;
    }

    /**
     * Convenience factory for "tenant not found" scenarios.
     *
     * @param  string $tenantId The UUID or slug that was looked up.
     * @return static
     */
    public static function notFound(string $tenantId): static
    {
        return new static(
            tenantId: $tenantId,
            message: sprintf('Tenant "%s" could not be found.', $tenantId),
            code: 404,
        );
    }

    /**
     * Convenience factory for "tenant suspended" scenarios.
     *
     * @param  string $tenantId The UUID of the suspended tenant.
     * @return static
     */
    public static function suspended(string $tenantId): static
    {
        return new static(
            tenantId: $tenantId,
            message: sprintf('Tenant "%s" is suspended and cannot process requests.', $tenantId),
            code: 403,
        );
    }

    /**
     * Convenience factory for cross-tenant access violations.
     *
     * @param  string $requestingTenantId UUID of the tenant making the request.
     * @param  string $targetTenantId     UUID of the tenant whose data was accessed.
     * @return static
     */
    public static function accessDenied(string $requestingTenantId, string $targetTenantId): static
    {
        return new static(
            tenantId: $requestingTenantId,
            message: sprintf(
                'Tenant "%s" attempted to access resources belonging to tenant "%s".',
                $requestingTenantId,
                $targetTenantId,
            ),
            code: 403,
        );
    }
}
