<?php

declare(strict_types=1);

namespace App\Domain\Inventory\Repositories;

use App\Domain\Inventory\Entities\Product;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

/**
 * Product repository contract — defines how product data is accessed and persisted.
 * Implementations must be tenant-aware and support flexible filtering/pagination.
 */
interface ProductRepositoryInterface
{
    /**
     * Find a product by its primary key.
     */
    public function findById(string|int $id): ?Product;

    /**
     * Find a product by its SKU within the current tenant scope.
     */
    public function findBySku(string $sku): ?Product;

    /**
     * Retrieve products with optional filtering, sorting, and conditional pagination.
     * Returns a LengthAwarePaginator when $perPage is provided, Collection otherwise.
     *
     * @param  array<string, mixed>  $filters  e.g. ['category_id'=>1, 'is_active'=>true]
     * @param  array<array{field:string,direction:string}>  $sorts
     * @param  array<string>  $with  eager-load relations
     */
    public function findAll(
        array $filters = [],
        array $sorts = [],
        ?int $perPage = null,
        int $page = 1,
        array $with = []
    ): Collection|LengthAwarePaginator;

    /**
     * Persist a new product and return the hydrated entity.
     *
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Product;

    /**
     * Update an existing product; throws ProductNotFoundException if missing.
     *
     * @param  array<string, mixed>  $data
     */
    public function update(string|int $id, array $data): Product;

    /**
     * Soft-delete a product; returns false when not found.
     */
    public function delete(string|int $id): bool;

    /**
     * Decrement available stock atomically; returns updated product.
     * Throws InsufficientStockException when stock would go negative.
     */
    public function decrementStock(string|int $id, int $quantity): Product;

    /**
     * Increment available stock atomically; returns updated product.
     */
    public function incrementStock(string|int $id, int $quantity): Product;

    /**
     * Reserve stock (moves qty from available to reserved).
     */
    public function reserveStock(string|int $id, int $quantity): Product;

    /**
     * Release previously reserved stock back to available.
     */
    public function releaseStock(string|int $id, int $quantity): Product;

    /**
     * Count matching products.
     *
     * @param  array<string, mixed>  $criteria
     */
    public function count(array $criteria = []): int;

    /**
     * Check whether a product exists for given criteria.
     *
     * @param  array<string, mixed>  $criteria
     */
    public function exists(array $criteria): bool;

    /**
     * Full-text search across name, SKU, and description with pagination support.
     */
    public function search(
        string $term,
        ?int $perPage = null,
        int $page = 1
    ): Collection|LengthAwarePaginator;
}
