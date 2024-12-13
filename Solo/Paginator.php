<?php declare(strict_types=1);

namespace Solo;

use Solo\Paginator\PaginationLink;
use Solo\Paginator\PaginationResult;
use Solo\Paginator\PerPageOption;

final class Paginator
{
    private const DEFAULT_PAGE = 1;
    private const DEFAULT_PER_PAGE = 25;
    private const MIN_LINKS = 3;
    private const DEFAULT_PER_PAGE_OPTIONS = [10, 25, 50, 100];

    public static function paginate(
        array  $queryParams,
        int    $totalItems,
        ?array $allowedPerPage = null,
        ?int $currentPage = null,
        ?int $currentPerPage = null,
    ): PaginationResult {
        $perPageOptions = !empty($allowedPerPage) ? $allowedPerPage : self::DEFAULT_PER_PAGE_OPTIONS;
        
        $perPage = $currentPerPage ?? (int)($queryParams['per_page'] ?? self::DEFAULT_PER_PAGE);
        if (!in_array($perPage, $perPageOptions)) {
            $perPage = self::DEFAULT_PER_PAGE;
        }

        $page = $currentPage ?? (int)($queryParams['page'] ?? self::DEFAULT_PAGE);
        $totalPages = (int)ceil($totalItems / $perPage);
        $currentPage = min(max(1, $page), $totalPages);

        return new PaginationResult(
            page: $currentPage,
            perPage: $perPage,
            totalPages: $totalPages,
            links: self::createPaginationLinks($queryParams, $currentPage, $totalPages),
            nextPageUrl: self::getNextPageUrl($queryParams, $currentPage, $totalPages),
            previousPageUrl: self::getPreviousPageUrl($queryParams, $currentPage),
            hasNextPage: $currentPage < $totalPages,
            hasPreviousPage: $currentPage > 1,
            perPageOptions: self::createPerPageOptions($queryParams, $perPage, $perPageOptions),
        );
    }

    private static function createPerPageOptions(
        array $queryParams,
        int   $currentPerPage,
        array $perPageOptions
    ): array {
        return array_map(
            fn(int $value) => new PerPageOption(
                value: $value,
                url: self::buildPerPageUrl($queryParams, $value),
                isCurrent: $value === $currentPerPage
            ),
            $perPageOptions
        );
    }

    private static function buildPerPageUrl(array $queryParams, int $perPage): string
    {
        unset($queryParams['page']);
        $queryParams['per_page'] = $perPage;

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
    ): array {
        if ($totalPages <= self::MIN_LINKS) {
            return self::createSequentialLinks($queryParams, 1, $totalPages, $currentPage);
        }

        return self::createLinksWithEllipsis($queryParams, $currentPage, $totalPages);
    }

    private static function createLinksWithEllipsis(
        array $queryParams,
        int   $currentPage,
        int   $totalPages,
    ): array {
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
    ): array {
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
    ): PaginationLink {
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
    ): ?string {
        if ($currentPage >= $totalPages) {
            return null;
        }

        return self::buildPageUrl($queryParams, $currentPage + 1);
    }

    private static function getPreviousPageUrl(
        array $queryParams,
        int   $currentPage
    ): ?string {
        if ($currentPage <= 1) {
            return null;
        }

        return self::buildPageUrl($queryParams, $currentPage - 1);
    }
}