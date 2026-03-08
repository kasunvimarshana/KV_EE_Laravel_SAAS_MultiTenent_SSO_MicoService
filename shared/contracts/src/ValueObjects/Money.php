<?php

declare(strict_types=1);

namespace KV\Shared\ValueObjects;

use InvalidArgumentException;
use OverflowException;

/**
 * Immutable monetary value stored as integer cents.
 *
 * Amounts are always stored as minor units (cents) to avoid floating-point
 * rounding errors.  Arithmetic operations return new instances rather than
 * mutating the existing one, making this safe to use in value chains.
 *
 * Example:
 * ```php
 * $price    = Money::ofFloat(19.99, 'USD'); // 1999 cents
 * $tax      = Money::ofFloat(2.00, 'USD');  //  200 cents
 * $subtotal = $price->add($tax);            // 2199 cents → $21.99
 * echo $subtotal->toString();               // "21.99 USD"
 * ```
 *
 * @psalm-immutable
 */
final class Money
{
    /** Maximum representable amount in cents to guard against integer overflow. */
    private const MAX_CENTS = PHP_INT_MAX;

    /**
     * @param  int    $amount   Amount in minor units (cents).  Must be >= 0.
     * @param  string $currency ISO 4217 currency code (3 uppercase letters), e.g. "USD".
     * @throws InvalidArgumentException When $amount is negative or $currency is malformed.
     */
    public function __construct(
        private readonly int $amount,
        private readonly string $currency,
    ) {
        if ($amount < 0) {
            throw new InvalidArgumentException('Money amount cannot be negative.');
        }

        if (!preg_match('/^[A-Z]{3}$/', $currency)) {
            throw new InvalidArgumentException(
                sprintf('"%s" is not a valid ISO 4217 currency code.', $currency)
            );
        }
    }

    /**
     * Create a Money instance from a float value.
     *
     * The float is rounded to two decimal places before converting to cents
     * to avoid floating-point representation artefacts.
     *
     * @param  float  $amount   Decimal amount, e.g. 19.99.
     * @param  string $currency ISO 4217 currency code.
     * @return static
     */
    public static function ofFloat(float $amount, string $currency): static
    {
        $cents = (int) round($amount * 100);

        return new static($cents, $currency);
    }

    /**
     * Create a Money instance directly from an integer cent value.
     *
     * @param  int    $cents    Amount in minor units.
     * @param  string $currency ISO 4217 currency code.
     * @return static
     */
    public static function ofCents(int $cents, string $currency): static
    {
        return new static($cents, $currency);
    }

    /**
     * Return a new Money instance with the combined amount of both operands.
     *
     * @param  Money $other Must use the same currency.
     * @return static
     * @throws InvalidArgumentException When currencies differ.
     * @throws OverflowException        When the result exceeds PHP_INT_MAX cents.
     */
    public function add(Money $other): static
    {
        $this->assertSameCurrency($other);

        $result = $this->amount + $other->amount;

        if ($result > self::MAX_CENTS) {
            throw new OverflowException('Money addition would overflow integer bounds.');
        }

        return new static($result, $this->currency);
    }

    /**
     * Return a new Money instance with the difference of both operands.
     *
     * @param  Money $other Must use the same currency and be <= this amount.
     * @return static
     * @throws InvalidArgumentException When currencies differ or $other > $this.
     */
    public function subtract(Money $other): static
    {
        $this->assertSameCurrency($other);

        $result = $this->amount - $other->amount;

        if ($result < 0) {
            throw new InvalidArgumentException('Money subtraction would result in a negative amount.');
        }

        return new static($result, $this->currency);
    }

    /**
     * Return a new Money instance scaled by the given factor.
     *
     * The result is rounded to the nearest cent using banker's rounding.
     *
     * @param  float $factor A positive multiplier, e.g. 1.2 for a 20 % markup.
     * @return static
     * @throws InvalidArgumentException When $factor is negative.
     */
    public function multiply(float $factor): static
    {
        if ($factor < 0) {
            throw new InvalidArgumentException('Multiplication factor cannot be negative.');
        }

        $result = (int) round($this->amount * $factor, 0, PHP_ROUND_HALF_EVEN);

        return new static($result, $this->currency);
    }

    /**
     * Return the amount as a float in major units (e.g. 19.99 for $19.99).
     */
    public function toFloat(): float
    {
        return $this->amount / 100;
    }

    /**
     * Return the raw integer amount in minor units (cents).
     */
    public function getAmount(): int
    {
        return $this->amount;
    }

    /**
     * Return the ISO 4217 currency code.
     */
    public function getCurrency(): string
    {
        return $this->currency;
    }

    /**
     * Return a human-readable string representation, e.g. "19.99 USD".
     */
    public function toString(): string
    {
        return sprintf('%.2f %s', $this->toFloat(), $this->currency);
    }

    /**
     * Alias of toString() that enables direct string casting.
     */
    public function __toString(): string
    {
        return $this->toString();
    }

    /**
     * Check value equality between two Money instances.
     *
     * @param  Money $other
     * @return bool  True only when both amount and currency are identical.
     */
    public function equals(Money $other): bool
    {
        return $this->amount === $other->amount
            && $this->currency === $other->currency;
    }

    /**
     * @throws InvalidArgumentException When currencies differ.
     */
    private function assertSameCurrency(Money $other): void
    {
        if ($this->currency !== $other->currency) {
            throw new InvalidArgumentException(
                sprintf(
                    'Cannot operate on different currencies: "%s" vs "%s".',
                    $this->currency,
                    $other->currency,
                )
            );
        }
    }
}
