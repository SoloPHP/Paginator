<?php

declare(strict_types=1);

namespace Solo\Paginator\Tests;

use PHPUnit\Framework\TestCase;
use Solo\Paginator\Paginator;
use Solo\Paginator\PaginationResult;
use Solo\Paginator\PaginationLink;
use Solo\Paginator\LimitOption;

class PaginatorTest extends TestCase
{
    public function testBasicPagination(): void
    {
        $queryParams = ['page' => '2', 'limit' => '10'];
        $result = Paginator::paginate($queryParams, 100);

        $this->assertInstanceOf(PaginationResult::class, $result);
        $this->assertEquals(2, $result->page);
        $this->assertEquals(10, $result->limit);
        $this->assertEquals(10, $result->totalPages);
        $this->assertEquals(100, $result->totalItems);
        $this->assertTrue($result->hasNextPage);
        $this->assertTrue($result->hasPreviousPage);
        $this->assertNotNull($result->nextPageUrl);
        $this->assertNotNull($result->previousPageUrl);
    }

    public function testFirstPage(): void
    {
        $queryParams = ['page' => '1', 'limit' => '25'];
        $result = Paginator::paginate($queryParams, 50);

        $this->assertEquals(1, $result->page);
        $this->assertEquals(25, $result->limit);
        $this->assertEquals(2, $result->totalPages);
        $this->assertFalse($result->hasPreviousPage);
        $this->assertTrue($result->hasNextPage);
        $this->assertNull($result->previousPageUrl);
        $this->assertNotNull($result->nextPageUrl);
    }

    public function testLastPage(): void
    {
        $queryParams = ['page' => '2', 'limit' => '25'];
        $result = Paginator::paginate($queryParams, 50);

        $this->assertEquals(2, $result->page);
        $this->assertEquals(25, $result->limit);
        $this->assertEquals(2, $result->totalPages);
        $this->assertTrue($result->hasPreviousPage);
        $this->assertFalse($result->hasNextPage);
        $this->assertNotNull($result->previousPageUrl);
        $this->assertNull($result->nextPageUrl);
    }

    public function testSinglePage(): void
    {
        $queryParams = ['page' => '1', 'limit' => '25'];
        $result = Paginator::paginate($queryParams, 20);

        $this->assertEquals(1, $result->page);
        $this->assertEquals(25, $result->limit);
        $this->assertEquals(1, $result->totalPages);
        $this->assertFalse($result->hasPreviousPage);
        $this->assertFalse($result->hasNextPage);
        $this->assertNull($result->previousPageUrl);
        $this->assertNull($result->nextPageUrl);
    }

    public function testInvalidPageNumber(): void
    {
        $queryParams = ['page' => '0', 'limit' => '25'];
        $result = Paginator::paginate($queryParams, 100);

        $this->assertEquals(1, $result->page);
    }

    public function testInvalidLimit(): void
    {
        $queryParams = ['page' => '1', 'limit' => '999'];
        $result = Paginator::paginate($queryParams, 100);

        $this->assertEquals(25, $result->limit); // Default limit
    }

    public function testCustomLimitOptions(): void
    {
        $queryParams = ['page' => '1', 'limit' => '20'];
        $limitOptions = [20, 40, 60];
        $result = Paginator::paginate($queryParams, 100, $limitOptions);

        $this->assertEquals(20, $result->limit);
        $this->assertCount(3, $result->limitOptions);

        foreach ($result->limitOptions as $option) {
            $this->assertInstanceOf(LimitOption::class, $option);
            $this->assertContains($option->value, $limitOptions);
        }
    }

    public function testPaginationLinks(): void
    {
        $queryParams = ['page' => '5', 'limit' => '10'];
        $result = Paginator::paginate($queryParams, 100);

        $this->assertNotEmpty($result->links);

        foreach ($result->links as $link) {
            $this->assertInstanceOf(PaginationLink::class, $link);
            $this->assertIsInt($link->page);
            $this->assertIsString($link->url);
            $this->assertIsBool($link->isCurrent);
            $this->assertIsBool($link->isEllipsis);
        }
    }

    public function testEllipsisLinks(): void
    {
        $queryParams = ['page' => '50', 'limit' => '1'];
        $result = Paginator::paginate($queryParams, 100);

        $hasEllipsis = false;
        foreach ($result->links as $link) {
            if ($link->isEllipsis) {
                $hasEllipsis = true;
                break;
            }
        }

        $this->assertTrue($hasEllipsis, 'Should have ellipsis links for large page counts');
    }

    public function testUrlGeneration(): void
    {
        $queryParams = ['page' => '2', 'limit' => '10', 'search' => 'test'];
        $result = Paginator::paginate($queryParams, 100);

        // Check that search parameter is preserved in URLs
        foreach ($result->links as $link) {
            if (!$link->isEllipsis) {
                $this->assertStringContainsString('search=test', $link->url);
            }
        }
    }

    public function testDefaultValues(): void
    {
        $result = Paginator::paginate([], 100);

        $this->assertEquals(1, $result->page);
        $this->assertEquals(25, $result->limit);
    }

    public function testPageExceedsTotalPages(): void
    {
        $queryParams = ['page' => '999', 'limit' => '10'];
        $result = Paginator::paginate($queryParams, 50);

        $this->assertEquals(5, $result->page); // Should be limited to total pages
        $this->assertEquals(5, $result->totalPages);
    }

    public function testLimitOptionsUrls(): void
    {
        $queryParams = ['page' => '2', 'limit' => '25', 'search' => 'test'];
        $result = Paginator::paginate($queryParams, 100);

        foreach ($result->limitOptions as $option) {
            $this->assertInstanceOf(LimitOption::class, $option);
            $this->assertStringContainsString('search=test', $option->url);
            $this->assertIsBool($option->isCurrent);
        }
    }
}
