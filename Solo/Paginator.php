<?php declare(strict_types=1);

namespace Solo;

class Paginator
{
    /** @var int Total number of items */
    private int $totalItems;

    /** @var int Number of items per page */
    private int $itemsPerPage;

    /** @var int Current page number */
    private int $currentPage;

    /** @var int Max links in pagination */
    private int $maxLinks;

    /** @var array Query parameters for URL */
    private array $queryParams;

    public function get(
        array $queryParams,
        int   $totalItems,
        int   $currentPage = 1,
        int   $itemsPerPage = 50,
        int   $maxLinks = 3
    ): array
    {
        $this->queryParams = $queryParams;
        $this->totalItems = $totalItems;
        $this->currentPage = $currentPage;
        $this->itemsPerPage = $itemsPerPage;
        $this->maxLinks = max(3, $maxLinks);

        return [
            'paginationLinks' => $this->generatePaginationLinks(),
            'hasPreviousPage' => $this->hasPreviousPage(),
            'hasNextPage' => $this->hasNextPage(),
            'previousPageUrl' => $this->getPreviousPageUrl(),
            'nextPageUrl' => $this->getNextPageUrl(),
            'totalPages' => $this->calculateTotalPages(),
            'page'=> $this->currentPage,
            'perPage' => $this->itemsPerPage,
        ];
    }

    private function calculateTotalPages(): int
    {
        return (int)ceil($this->totalItems / $this->itemsPerPage);
    }

    private function getPageUrl(int $page): string
    {
        $queryString = http_build_query(array_merge($this->queryParams, ['page' => $page]));
        return '?' . $queryString;
    }

    private function generatePaginationLinks(): array
    {
        $totalPages = $this->calculateTotalPages();
        $currentPage = $this->currentPage;
        $paginationLinks = [];

        if ($totalPages <= $this->maxLinks) {
            for ($i = 1; $i <= $totalPages; $i++) {
                $paginationLinks[] = $this->createPaginationLink($i);
            }
            return $paginationLinks;
        }

        $halfMaxLinks = intdiv($this->maxLinks, 2);
        $start = max(1, $currentPage - $halfMaxLinks);
        $end = min($totalPages, $start + $this->maxLinks - 1);

        if ($start > 1) {
            $paginationLinks[] = $this->createPaginationLink(1);
            if ($start > 2) {
                $paginationLinks[] = ['isEllipsis' => true];
            }
        }

        for ($i = $start; $i <= $end; $i++) {
            $paginationLinks[] = $this->createPaginationLink($i);
        }

        if ($end < $totalPages) {
            if ($end < $totalPages - 1) {
                $paginationLinks[] = ['isEllipsis' => true];
            }
            $paginationLinks[] = $this->createPaginationLink($totalPages);
        }

        return $paginationLinks;
    }

    private function createPaginationLink(int $page): array
    {
        return [
            'page' => $page,
            'url' => $this->getPageUrl($page),
            'isCurrent' => ($page === $this->currentPage),
        ];
    }

    private function hasPreviousPage(): bool
    {
        return $this->currentPage > 1;
    }

    private function hasNextPage(): bool
    {
        return $this->currentPage < $this->calculateTotalPages();
    }

    private function getPreviousPageUrl(): ?string
    {
        return $this->hasPreviousPage() ? $this->getPageUrl($this->currentPage - 1) : null;
    }

    private function getNextPageUrl(): ?string
    {
        return $this->hasNextPage() ? $this->getPageUrl($this->currentPage + 1) : null;
    }
}