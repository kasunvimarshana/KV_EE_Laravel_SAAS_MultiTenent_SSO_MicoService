<?php

declare(strict_types=1);

namespace KV\Shared\DTOs;

/**
 * Pagination envelope that wraps a page of results with metadata.
 *
 * This DTO is returned by repository methods that accept $perPage / $page
 * parameters, and is designed to serialise directly into a standard JSON
 * pagination response.
 */
final class PaginationDTO
{
    /**
     * @param  array<int, mixed> $data        The items on the current page.
     * @param  int               $total       Total number of matching records.
     * @param  int|null          $perPage     Number of items per page; null means unpaginated.
     * @param  int               $currentPage 1-based index of the current page.
     * @param  int               $lastPage    1-based index of the final page.
     * @param  int               $from        1-based offset of the first item on this page.
     * @param  int               $to          1-based offset of the last item on this page.
     * @param  bool              $hasMorePages Whether additional pages exist after this one.
     */
    public function __construct(
        public readonly array $data,
        public readonly int $total,
        public readonly ?int $perPage,
        public readonly int $currentPage,
        public readonly int $lastPage,
        public readonly int $from,
        public readonly int $to,
        public readonly bool $hasMorePages,
    ) {}

    /**
     * Construct a PaginationDTO from a flat item list and pagination parameters.
     *
     * All offset / lastPage / hasMorePages values are derived automatically.
     *
     * @param  array<int, mixed> $items   The items on the requested page (already sliced).
     * @param  int               $total   Total number of records across all pages.
     * @param  int|null          $perPage Items per page; when null the whole result-set is on page 1.
     * @param  int               $page    1-based page number requested.
     * @return static
     */
    public static function fromArray(
        array $items,
        int $total,
        ?int $perPage,
        int $page,
    ): static {
        if ($perPage === null || $perPage <= 0) {
            return new static(
                data: $items,
                total: $total,
                perPage: null,
                currentPage: 1,
                lastPage: 1,
                from: $total > 0 ? 1 : 0,
                to: $total,
                hasMorePages: false,
            );
        }

        $lastPage = (int) ceil($total / $perPage);
        $lastPage = max($lastPage, 1);
        $page     = max($page, 1);
        $count    = count($items);
        $from     = $total > 0 ? ($page - 1) * $perPage + 1 : 0;
        $to       = $total > 0 ? $from + $count - 1 : 0;

        return new static(
            data: $items,
            total: $total,
            perPage: $perPage,
            currentPage: $page,
            lastPage: $lastPage,
            from: $from,
            to: $to,
            hasMorePages: $page < $lastPage,
        );
    }

    /**
     * Serialise to a plain associative array for JSON responses.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'data'           => $this->data,
            'total'          => $this->total,
            'per_page'       => $this->perPage,
            'current_page'   => $this->currentPage,
            'last_page'      => $this->lastPage,
            'from'           => $this->from,
            'to'             => $this->to,
            'has_more_pages' => $this->hasMorePages,
        ];
    }
}
