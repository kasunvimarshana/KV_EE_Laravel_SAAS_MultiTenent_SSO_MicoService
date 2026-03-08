<?php

declare(strict_types=1);

namespace KV\Shared\Interfaces;

/**
 * Tenant context contract.
 *
 * Represents the currently resolved tenant and exposes everything a service
 * needs to operate in a multi-tenant context: database routing, cache
 * namespacing, and tenant-specific feature/configuration flags.
 */
interface TenantInterface
{
    /**
     * Return the globally unique tenant UUID.
     *
     * @return string UUID v4 string, e.g. "550e8400-e29b-41d4-a716-446655440000".
     */
    public function getTenantId(): string;

    /**
     * Return the URL-safe slug that identifies the tenant in routes and hostnames.
     *
     * @return string Lowercase alphanumeric slug, e.g. "acme-corp".
     */
    public function getTenantSlug(): string;

    /**
     * Return the name of the Laravel database connection that should be used
     * for this tenant's data.
     *
     * Implementations may return a static connection name for shared-database
     * strategies or a dynamically constructed name for database-per-tenant
     * strategies.
     *
     * @return string Laravel connection name as declared in config/database.php.
     */
    public function getDatabaseConnection(): string;

    /**
     * Return a cache key prefix scoped to this tenant.
     *
     * All cache operations for tenant-specific data should be prefixed with
     * this value to prevent cross-tenant data leakage.
     *
     * @return string e.g. "tenant:550e8400-e29b-41d4-a716-446655440000:".
     */
    public function getCachePrefix(): string;

    /**
     * Read a tenant-level configuration value using dot-notation.
     *
     * Tenant config is layered on top of global application config, allowing
     * per-tenant feature flags, plan limits, and integration settings.
     *
     * @param  string $key     Dot-notation config key, e.g. "features.invoicing".
     * @param  mixed  $default Value to return when the key is not set.
     * @return mixed
     */
    public function getConfig(string $key, mixed $default = null): mixed;
}
