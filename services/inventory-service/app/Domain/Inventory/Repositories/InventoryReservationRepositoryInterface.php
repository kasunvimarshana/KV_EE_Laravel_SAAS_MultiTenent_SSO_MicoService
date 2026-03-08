<?php

declare(strict_types=1);

namespace App\Domain\Inventory\Repositories;

use App\Domain\Inventory\Entities\InventoryReservation;
use Illuminate\Support\Collection;

/**
 * Contract for managing inventory reservations used in Saga transactions.
 */
interface InventoryReservationRepositoryInterface
{
    public function findById(string|int $id): ?InventoryReservation;

    public function findByReservationId(string $reservationId): ?InventoryReservation;

    public function findByOrderId(string $orderId): ?InventoryReservation;

    /** @param  array<string, mixed>  $data */
    public function create(array $data): InventoryReservation;

    /** @param  array<string, mixed>  $data */
    public function update(string|int $id, array $data): InventoryReservation;

    public function findExpired(): Collection;

    /** Returns reservations by status: pending, confirmed, released, expired */
    public function findByStatus(string $status): Collection;
}
