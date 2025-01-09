<?php declare(strict_types=1);

namespace Solo;

use Solo\Paginator\PaginationLink;
use Solo\Paginator\PaginationResult;
use Solo\Paginator\LimitOption;

final class Paginator
{
    private const DEFAULT_PAGE = 1;
    private const DEFAULT_LIMIT = 25;
    private const MIN_LINKS = 3;
    private const DEFAULT_LIMIT_OPTIONS = [10, 25, 50, 100];

    public static function paginate(
        array  $queryParams,
        int    $totalItems,
        ?array $allowedLimitOptions = null
    ): PaginationResult
    {
        $limitOptions = $allowedLimitOptions ?? self::DEFAULT_LIMIT_OPTIONS;

        $limit = (int)($queryParams['limit'] ?? self::DEFAULT_LIMIT);
        if (!in_array($limit, $limitOptions)) {
            $limit = self::DEFAULT_LIMIT;
        }

        $page = (int)($queryParams['page'] ?? self::DEFAULT_PAGE);
        $totalPages = (int)ceil($totalItems / $limit);
        $currentPage = min(max(1, $page), $totalPages);

        return new PaginationResult(
            page: $currentPage,
            limit: $limit,
            totalPages: $totalPages,
            totalItems: $totalItems,
            links: self::createPaginationLinks($queryParams, $currentPage, $totalPages),
            nextPageUrl: self::getNextPageUrl($queryParams, $currentPage, $totalPages),
            previousPageUrl: self::getPreviousPageUrl($queryParams, $currentPage),
            hasNextPage: $currentPage < $totalPages,
            hasPreviousPage: $currentPage > 1,
            limitOptions: self::createLimitOptions($queryParams, $limit, $limitOptions),
        );
    }

    private static function createLimitOptions(
        array $queryParams,
        int   $currentLimit,
        array $limitOptions
    ): array
    {
        return array_map(
            fn(int $value) => new LimitOption(
                value: $value,
                url: self::buildLimitUrl($queryParams, $value),
                isCurrent: $value === $currentLimit
            ),
            $limitOptions
        );
    }

    private static function buildLimitUrl(array $queryParams, int $limit): string
    {
        unset($queryParams['page']);
        $queryParams['limit'] = $limit;

        return '?' . http_build_query($queryParams);
    }

    private static function buildPageUrl(array $queryParams, int $page): string
    {
        $queryParams['page'] = $page;
        return '?' . http_build_query($queryParams);
    }

    private static function createPaginationLinks(
        array $queryParams,
        int   $currentPage,
        int   $totalPages,
    ): array
    {
        if ($totalPages <= self::MIN_LINKS) {
            return self::createSequentialLinks($queryParams, 1, $totalPages, $currentPage);
        }

        return self::createLinksWithEllipsis($queryParams, $currentPage, $totalPages);
    }

    private static function createLinksWithEllipsis(
        array $queryParams,
        int   $currentPage,
        int   $totalPages,
    ): array
    {
        $links = [];
        $start = max(1, $currentPage - 1);
        $end = min($totalPages, $start + self::MIN_LINKS - 1);

        if ($start > 1) {
            $links[] = self::createLink($queryParams, 1, $currentPage);
            if ($start > 2) {
                $links[] = self::createEllipsisLink();
            }
        }

        $links = [...$links, ...self::createSequentialLinks($queryParams, $start, $end, $currentPage)];

        if ($end < $totalPages) {
            if ($end < $totalPages - 1) {
                $links[] = self::createEllipsisLink();
            }
            $links[] = self::createLink($queryParams, $totalPages, $currentPage);
        }

        return $links;
    }

    private static function createSequentialLinks(
        array $queryParams,
        int   $start,
        int   $end,
        int   $currentPage,
    ): array
    {
        $links = [];
        for ($page = $start; $page <= $end; $page++) {
            $links[] = self::createLink($queryParams, $page, $currentPage);
        }
        return $links;
    }

    private static function createLink(
        array $queryParams,
        int   $page,
        int   $currentPage,
    ): PaginationLink
    {
        return new PaginationLink(
            page: $page,
            url: self::buildPageUrl($queryParams, $page),
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

    private static function getNextPageUrl(
        array $queryParams,
        int   $currentPage,
        int   $totalPages
    ): ?string
    {
        if ($currentPage >= $totalPages) {
            return null;
        }

        return self::buildPageUrl($queryParams, $currentPage + 1);
    }

    private static function getPreviousPageUrl(
        array $queryParams,
        int   $currentPage
    ): ?string
    {
        if ($currentPage <= 1) {
            return null;
        }

        return self::buildPageUrl($queryParams, $currentPage - 1);
    }
}