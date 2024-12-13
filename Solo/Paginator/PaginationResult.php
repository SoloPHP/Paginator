<?php

namespace Solo\Paginator;

readonly class PaginationResult
{
    public function __construct(
        public int     $page,
        public int     $perPage,
        public int     $totalPages,
        public array   $links,
        public ?string $nextPageUrl,
        public ?string $previousPageUrl,
        public bool    $hasNextPage,
        public bool    $hasPreviousPage,
        public array   $perPageOptions,
    )
    {
    }
}