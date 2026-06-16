<?php

declare(strict_types=1);

namespace Inwebo\PaginatorBundle\Tests;

use Inwebo\PaginatorBundle\Paginated;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Paginated::class)]
final class PaginatedTest extends TestCase
{
    private function createPaginatedState(int $currentPage, int $numResults, int $pageSize = Paginated::PAGE_SIZE): Paginated
    {
        $paginated = new PaginatedStub($currentPage, $numResults, $pageSize);

        return $paginated;
    }

    public function testGetLastPage(): void
    {
        $paginated = $this->createPaginatedState(1, 100, 10);

        $this->assertSame(10, $paginated->getLastPage());
    }

    public function testGetLastPageWithRemainder(): void
    {
        $paginated = $this->createPaginatedState(1, 95, 10);

        $this->assertSame(10, $paginated->getLastPage());
    }

    public function testGetLastPageSinglePage(): void
    {
        $paginated = $this->createPaginatedState(1, 5, 10);

        $this->assertSame(1, $paginated->getLastPage());
    }

    public function testHasPreviousPage(): void
    {
        $this->assertFalse($this->createPaginatedState(1, 100, 10)->hasPreviousPage());
        $this->assertTrue($this->createPaginatedState(2, 100, 10)->hasPreviousPage());
        $this->assertTrue($this->createPaginatedState(5, 100, 10)->hasPreviousPage());
    }

    public function testGetPreviousPage(): void
    {
        $this->assertSame(1, $this->createPaginatedState(1, 100, 10)->getPreviousPage());
        $this->assertSame(1, $this->createPaginatedState(2, 100, 10)->getPreviousPage());
        $this->assertSame(4, $this->createPaginatedState(5, 100, 10)->getPreviousPage());
    }

    public function testHasNextPage(): void
    {
        $this->assertTrue($this->createPaginatedState(1, 100, 10)->hasNextPage());
        $this->assertTrue($this->createPaginatedState(9, 100, 10)->hasNextPage());
        $this->assertFalse($this->createPaginatedState(10, 100, 10)->hasNextPage());
    }

    public function testGetNextPage(): void
    {
        $this->assertSame(2, $this->createPaginatedState(1, 100, 10)->getNextPage());
        $this->assertSame(10, $this->createPaginatedState(9, 100, 10)->getNextPage());
        $this->assertSame(10, $this->createPaginatedState(10, 100, 10)->getNextPage());
    }

    public function testHasToPaginate(): void
    {
        $this->assertTrue($this->createPaginatedState(1, 31, 30)->hasToPaginate());
        $this->assertFalse($this->createPaginatedState(1, 30, 30)->hasToPaginate());
        $this->assertFalse($this->createPaginatedState(1, 5, 30)->hasToPaginate());
    }

    public function testGetNumResults(): void
    {
        $this->assertSame(95, $this->createPaginatedState(1, 95, 10)->getNumResults());
    }

    public function testGetCurrentPage(): void
    {
        $this->assertSame(3, $this->createPaginatedState(3, 100, 10)->getCurrentPage());
    }

    public function testGetPageSize(): void
    {
        $this->assertSame(10, $this->createPaginatedState(1, 100, 10)->getPageSize());
        $this->assertSame(Paginated::PAGE_SIZE, $this->createPaginatedState(1, 100)->getPageSize());
    }

    public function testGetCountPagesSyncWithLastPage(): void
    {
        $paginated = $this->createPaginatedState(1, 100, 10);

        $this->assertSame($paginated->getLastPage(), $paginated->getCountPages());
    }
}
