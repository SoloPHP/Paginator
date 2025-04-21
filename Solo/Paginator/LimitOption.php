<?php

namespace Solo\Paginator;

readonly class LimitOption
{
    public function __construct(
        public int    $value,
        public string $url,
        public bool   $isCurrent,
    )
    {
    }
}