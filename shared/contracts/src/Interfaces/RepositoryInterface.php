<?php

declare(strict_types=1);

namespace KV\Shared\Interfaces;

/**
 * Base repository interface defining the standard CRUD contract
 * for all data-access repositories across microservices.
 *
 * Implementations should handle tenant isolation internally
 * (e.g., by scoping queries to the active tenant connection).
 */
interface RepositoryInterface
{
    /**
     * Find a single record by its primary identifier.
     *
     * @param  string|int $id  The primary key value.
     * @return object|null     The found entity, or null when not found.
     */
    public function findById(string|int $id): ?object;

    /**
     * Retrieve all records, optionally filtered, sorted, and paginated.
     *
     * @param  array    $filters  Associative array of column => value conditions.
     * @param  array    $sorts    Associative array of column => 'asc'|'desc'.
     * @param  int|null $perPage  Number of items per page; null returns all records.
     * @param  int      $page     1-based page number when $perPage is set.
     * @return mixed              Collection / array of entities, or a PaginationDTO.
     */
    public function findAll(
        array $filters = [],
        array $sorts = [],
        ?int $perPage = null,
        int $page = 1,
    ): mixed;

    /**
     * Find records matching arbitrary criteria.
     *
     * @param  array    $criteria Associative array of column => value conditions.
     * @param  array    $sorts    Associative array of column => 'asc'|'desc'.
     * @param  int|null $perPage  Number of items per page; null returns all matches.
     * @param  int      $page     1-based page number when $perPage is set.
     * @return mixed              Collection / array of entities, or a PaginationDTO.
     */
    public function findBy(
        array $criteria,
        array $sorts = [],
        ?int $perPage = null,
        int $page = 1,
    ): mixed;

    /**
     * Persist a new record and return the hydrated entity.
     *
     * @param  array  $data Column => value pairs for the new record.
     * @return object       The newly created entity.
     */
    public function create(array $data): object;

    /**
     * Update an existing record and return the refreshed entity.
     *
     * @param  string|int $id   The primary key of the record to update.
     * @param  array      $data Column => value pairs to update.
     * @return object           The updated entity.
     *
     * @throws \RuntimeException When the record does not exist.
     */
    public function update(string|int $id, array $data): object;

    /**
     * Delete a record by its primary identifier.
     *
     * @param  string|int $id The primary key of the record to delete.
     * @return bool           True on success, false when the record was not found.
     */
    public function delete(string|int $id): bool;

    /**
     * Determine whether at least one record matches the given criteria.
     *
     * @param  array $criteria Associative array of column => value conditions.
     * @return bool
     */
    public function exists(array $criteria): bool;

    /**
     * Count records matching the given criteria.
     *
     * @param  array $criteria Associative array of column => value conditions.
     *                         An empty array counts all records.
     * @return int
     */
    public function count(array $criteria = []): int;
}
