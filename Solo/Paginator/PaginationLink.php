<?php

namespace Solo\Paginator;

readonly class PaginationLink
{
    public function __construct(
        public int    $page,
        public string $url,
        public bool   $isCurrent,
        public bool   $isEllipsis = false,
    )
    {
    }
}