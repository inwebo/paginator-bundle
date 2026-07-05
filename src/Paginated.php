<?php

declare(strict_types=1);

namespace Inwebo\PaginatorBundle;

use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\CountWalker;
use Doctrine\ORM\Tools\Pagination\Paginator as DoctrinePaginator;

class Paginated
{
    final public const int PAGE_SIZE = 30;
    final public const int DISPLAY_PAGES = 11;

    private int $currentPage = 1;
    private int $numResults;
    private int $countPages;

    /**
     * @var \Traversable<array-key, object>
     */
    private \Traversable $results;

    /**
     * @param \Closure(): int|null $countResolver Required when $lastPageFirst is true. Must return the total number of results.
     */
    public function __construct(
        private readonly QueryBuilder $queryBuilder,
        private readonly int $pageSize = self::PAGE_SIZE,
        private bool $lastPageFirst = false,
        private readonly ?\Closure $countResolver = null,
    ) {
        if ($this->lastPageFirst) {
            if (null === $this->countResolver) {
                throw new \LogicException('A $countResolver must be provided when $lastPageFirst is true.');
            }
            $this->countPages = (int) ceil(($this->countResolver)() / $this->pageSize);
        }
    }

    public function isLastPageFirst(): bool
    {
        return $this->lastPageFirst;
    }

    public function setLastPageFirst(bool $lastPageFirst): self
    {
        $this->lastPageFirst = $lastPageFirst;

        return $this;
    }

    public function getCountPages(): int
    {
        return $this->countPages;
    }

    /**
     * @return \Traversable<object>
     */
    public function getReverseIterator(): \Traversable
    {
        $iterable = iterator_to_array($this->results);

        for (end($iterable); ($key = key($iterable)) !== null; prev($iterable)) {
            yield $key => $iterable[$key];
        }
    }

    /**
     * @throws \Exception
     */
    public function paginate(?int $page = null): self
    {
        if ($this->isLastPageFirst() && null === $page) {
            $this->currentPage = max(1, $this->countPages);
        } else {
            $this->currentPage = max(1, (int) $page);
        }

        $firstResult = ($this->currentPage - 1) * $this->pageSize;

        $query = $this->queryBuilder
            ->setFirstResult($firstResult)
            ->setMaxResults($this->pageSize)
            ->getQuery();

        /** @var array<string, mixed> $joinDqlParts */
        $joinDqlParts = $this->queryBuilder->getDQLPart('join');

        if (0 === \count($joinDqlParts)) {
            $query->setHint(CountWalker::HINT_DISTINCT, false);
        }

        /** @var DoctrinePaginator<object> $paginator */
        $paginator = new DoctrinePaginator($query, true);

        /** @var array<string, mixed> $havingDqlParts */
        $havingDqlParts = $this->queryBuilder->getDQLPart('having');

        $useOutputWalkers = \count($havingDqlParts ?: []) > 0;
        $paginator->setUseOutputWalkers($useOutputWalkers);

        $this->results = $paginator->getIterator();
        $this->numResults = $paginator->count();

        if (!$this->lastPageFirst) {
            $this->countPages = $this->getLastPage();
        }

        return $this;
    }

    public function getCurrentPage(): int
    {
        return $this->currentPage;
    }

    public function getLastPage(): int
    {
        return (int) ceil($this->numResults / $this->pageSize);
    }

    public function getPageSize(): int
    {
        return $this->pageSize;
    }

    public function hasPreviousPage(): bool
    {
        return $this->currentPage > 1;
    }

    public function getPreviousPage(): int
    {
        return max(1, $this->currentPage - 1);
    }

    public function hasNextPage(): bool
    {
        return $this->currentPage < $this->getLastPage();
    }

    public function getNextPage(): int
    {
        return min($this->getLastPage(), $this->currentPage + 1);
    }

    public function hasToPaginate(): bool
    {
        return $this->numResults > $this->pageSize;
    }

    public function getNumResults(): int
    {
        return $this->numResults;
    }

    /**
     * @return \Traversable<int, object>
     */
    public function getResults(): \Traversable
    {
        return $this->results;
    }
}
