<?php

declare(strict_types=1);

namespace Inwebo\PaginatorBundle\Doctrine;

use Doctrine\ORM\QueryBuilder;
use Inwebo\PaginatorBundle\Paginated;

trait PaginationRepositoryTrait
{
    public function paginate(QueryBuilder $queryBuilder, int $page, int $pageSize = Paginated::PAGE_SIZE): Paginated
    {
        return (new Paginated($queryBuilder, $pageSize))->paginate($page);
    }

    /**
     * @param \Closure(): int|null $countResolver Required when $lastPageFirst is true. Must return the total number of results.
     */
    public function paginated(
        QueryBuilder $queryBuilder,
        int $pageSize = Paginated::PAGE_SIZE,
        bool $lastPageFirst = false,
        ?\Closure $countResolver = null,
    ): Paginated {
        return new Paginated($queryBuilder, $pageSize, $lastPageFirst, $countResolver);
    }
}
