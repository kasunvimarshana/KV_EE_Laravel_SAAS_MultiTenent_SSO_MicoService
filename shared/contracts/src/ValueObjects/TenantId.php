<?php

declare(strict_types=1);

namespace KV\Shared\ValueObjects;

use InvalidArgumentException;

/**
 * Immutable value object representing a tenant's unique identifier.
 *
 * Enforces UUID v4 format on construction, ensuring that tenant IDs
 * can never be created in an invalid state.  Equality is determined
 * by value, not reference.
 *
 * @psalm-immutable
 */
final class TenantId
{
    /** Regex for a canonical UUID v4 (case-insensitive). */
    private const UUID_PATTERN = '/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i';

    /**
     * @param  string $value A valid UUID v4 string.
     * @throws InvalidArgumentException When $value is not a valid UUID v4.
     */
    public function __construct(private readonly string $value)
    {
        if (!preg_match(self::UUID_PATTERN, $value)) {
            throw new InvalidArgumentException(
                sprintf('"%s" is not a valid UUID v4 and cannot be used as a TenantId.', $value)
            );
        }
    }

    /**
     * Named constructor — preferred over `new TenantId()` for readability.
     *
     * @param  string $value A valid UUID v4 string.
     * @return static
     * @throws InvalidArgumentException When $value is not a valid UUID v4.
     */
    public static function from(string $value): static
    {
        return new static($value);
    }

    /**
     * Return the UUID string in its canonical lowercase form.
     */
    public function toString(): string
    {
        return strtolower($this->value);
    }

    /**
     * Alias of toString() that enables direct string casting.
     */
    public function __toString(): string
    {
        return $this->toString();
    }

    /**
     * Check value equality between two TenantId instances.
     *
     * @param  TenantId $other The instance to compare against.
     * @return bool            True when both instances represent the same UUID.
     */
    public function equals(TenantId $other): bool
    {
        return strtolower($this->value) === strtolower($other->value);
    }
}
