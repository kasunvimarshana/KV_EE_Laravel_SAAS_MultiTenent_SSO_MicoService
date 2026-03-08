<?php

declare(strict_types=1);

namespace App\Domain\Inventory\Repositories;

use App\Domain\Inventory\Entities\StockMovement;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

/**
 * Contract for persisting and querying stock movement records.
 */
interface StockMovementRepositoryInterface
{
    public function findById(string|int $id): ?StockMovement;

    /**
     * @param  array<string, mixed>  $filters
     * @param  array<array{field:string,direction:string}>  $sorts
     */
    public function findAll(
        array $filters = [],
        array $sorts = [],
        ?int $perPage = null,
        int $page = 1
    ): Collection|LengthAwarePaginator;

    /** @param  array<string, mixed>  $data */
    public function create(array $data): StockMovement;

    public function findByProduct(
        string|int $productId,
        ?int $perPage = null,
        int $page = 1
    ): Collection|LengthAwarePaginator;

    public function findByReference(string $referenceType, string $referenceId): Collection;
}
