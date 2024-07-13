<?php declare(strict_types=1);

namespace Solo;

class Paginator
{
    /** URL pattern with {id} placeholder as a page number */
    private string $urlPattern;
    /** Total number of items  */
    private int $totalItems;
    /** Number of items per page */
    private int $itemsPerPage;
    /** Current page number */
    private int $currentPage;
    /** Max links in pagination */
    private int $maxLinks;

    public function get(
        string $urlPattern,
        int    $totalItems,
        int    $itemsPerPage = 50,
        int    $currentPage = 1,
        int    $maxLinks = 3
    ): array
    {
        $this->urlPattern = $urlPattern;
        $this->totalItems = $totalItems;
        $this->itemsPerPage = $itemsPerPage;
        $this->currentPage = $currentPage;
        $this->maxLinks = max(3, $maxLinks);

        $pagination['paginationLinks'] = $this->generatePaginationLinks();
        $pagination['hasPreviousPage'] = $this->hasPreviousPage();
        $pagination['hasNextPage'] = $this->hasNextPage();
        $pagination['previousPageUrl'] = $this->getPreviousPageUrl();
        $pagination['nextPageUrl'] = $this->getNextPageUrl();
        $pagination['totalPages'] = $this->calculateTotalPages();
        return $pagination;
    }

    private function calculateTotalPages(): float
    {
        return ceil($this->totalItems / $this->itemsPerPage);
    }

    private function getPageUrl($page): string
    {
        return str_replace('{id}', (string)$page, $this->urlPattern);
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

        $halfMaxLinks = floor($this->maxLinks / 2);
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

    private function createPaginationLink($page): array
    {
        return [
            'page' => $page,
            'url' => $this->getPageUrl($page),
            'isCurrent' => ($page == $this->currentPage),
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