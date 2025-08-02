<?php

declare(strict_types=1);

namespace Solo\Paginator;

readonly class PaginationResult
{
    public function __construct(
        public int $page,
        public int $limit,
        public int $totalPages,
        public int $totalItems,
        public array $links,
        public ?string $nextPageUrl,
        public ?string $previousPageUrl,
        public bool $hasNextPage,
        public bool $hasPreviousPage,
        public array $limitOptions,
    ) {
    }
}
