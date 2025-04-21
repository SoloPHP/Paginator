<?php declare(strict_types=1);

namespace Solo;

use Solo\Paginator\PaginationLink;
use Solo\Paginator\PaginationResult;
use Solo\Paginator\LimitOption;

final class Paginator
{
    private const MIN_LINKS = 3;
    public const DEFAULT_PAGE = 1;
    public const DEFAULT_LIMIT = 25;

    public static function paginate(
        array $queryParams,
        int $totalItems,
        array $limitOptions = [10, 25, 50, 100]
    ): PaginationResult
    {
        $limit = self::getLimit($queryParams, $limitOptions);
        $page = self::getPage($queryParams);
        $totalPages = (int)ceil($totalItems / $limit);
        $currentPage = min($page, $totalPages);

        return new PaginationResult(
            page: $currentPage,
            limit: $limit,
            totalPages: $totalPages,
            totalItems: $totalItems,
            links: self::createPaginationLinks($queryParams, $currentPage, $totalPages, $limitOptions),
            nextPageUrl: $currentPage < $totalPages ? self::buildUrl($queryParams, ['page' => $currentPage + 1]) : null,
            previousPageUrl: $currentPage > 1 ? self::buildUrl($queryParams, ['page' => $currentPage - 1]) : null,
            hasNextPage: $currentPage < $totalPages,
            hasPreviousPage: $currentPage > 1,
            limitOptions: array_map(
                fn(int $opt) => new LimitOption(
                    value: $opt,
                    url: self::buildUrl($queryParams, ['limit' => $opt, 'page' => null]),
                    isCurrent: $opt === $limit
                ),
                $limitOptions
            )
        );
    }

    private static function getLimit(array $queryParams, array $limitOptions): int
    {
        $limit = (int)($queryParams['limit'] ?? self::DEFAULT_LIMIT);
        return in_array($limit, $limitOptions, true)
            ? $limit
            : self::DEFAULT_LIMIT;
    }

    private static function getPage(array $queryParams): int
    {
        return max(1, (int)($queryParams['page'] ?? self::DEFAULT_PAGE));
    }

    private static function buildUrl(array $queryParams, array $override = []): string
    {
        $merged = array_merge($queryParams, $override);
        $filtered = [];

        foreach ($merged as $key => $value) {
            if ($value === null) {
                continue;
            }

            $filtered[$key] = $value;
        }

        return '?' . http_build_query($filtered);
    }

    private static function createPaginationLinks(array $queryParams, int $currentPage, int $totalPages, array $limitOptions): array
    {
        if ($totalPages <= self::MIN_LINKS) {
            return self::createSequentialLinks($queryParams, 1, $totalPages, $currentPage, $limitOptions);
        }

        return self::createLinksWithEllipsis($queryParams, $currentPage, $totalPages, $limitOptions);
    }

    private static function createLinksWithEllipsis(array $queryParams, int $currentPage, int $totalPages, array $limitOptions): array
    {
        $links = [];
        $start = max(1, $currentPage - 1);
        $end = min($totalPages, $start + self::MIN_LINKS - 1);

        if ($start > 1) {
            $links[] = self::createLink($queryParams, 1, $currentPage, $limitOptions);
            if ($start > 2) {
                $links[] = self::createEllipsisLink();
            }
        }

        $links = [...$links, ...self::createSequentialLinks($queryParams, $start, $end, $currentPage, $limitOptions)];

        if ($end < $totalPages) {
            if ($end < $totalPages - 1) {
                $links[] = self::createEllipsisLink();
            }
            $links[] = self::createLink($queryParams, $totalPages, $currentPage, $limitOptions);
        }

        return $links;
    }

    private static function createSequentialLinks(array $queryParams, int $start, int $end, int $currentPage, array $limitOptions): array
    {
        $links = [];
        for ($page = $start; $page <= $end; $page++) {
            $links[] = self::createLink($queryParams, $page, $currentPage, $limitOptions);
        }
        return $links;
    }

    private static function createLink(array $queryParams, int $page, int $currentPage, array $limitOptions): PaginationLink
    {
        return new PaginationLink(
            page: $page,
            url: self::buildUrl($queryParams, ['page' => $page]),
            isCurrent: $page === $currentPage,
        );
    }

    private static function createEllipsisLink(): PaginationLink
    {
        return new PaginationLink(
            page: 0,
            url: '',
            isCurrent: false,
            isEllipsis: true,
        );
    }
}