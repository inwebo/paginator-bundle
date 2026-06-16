<?php

declare(strict_types=1);

namespace Inwebo\PaginatorBundle\Tests;

use Inwebo\PaginatorBundle\Paginated;

/**
 * Test stub that bypasses the Doctrine dependency by injecting state directly.
 */
final class PaginatedStub extends Paginated
{
    public function __construct(int $currentPage, int $numResults, int $pageSize = Paginated::PAGE_SIZE)
    {
        $reflection = new \ReflectionClass(Paginated::class);

        $currentPageProp = $reflection->getProperty('currentPage');
        $currentPageProp->setValue($this, $currentPage);

        $numResultsProp = $reflection->getProperty('numResults');
        $numResultsProp->setValue($this, $numResults);

        $pageSizeProp = $reflection->getProperty('pageSize');
        $pageSizeProp->setValue($this, $pageSize);

        $countPagesProp = $reflection->getProperty('countPages');
        $countPagesProp->setValue($this, (int) ceil($numResults / $pageSize));
    }
}
