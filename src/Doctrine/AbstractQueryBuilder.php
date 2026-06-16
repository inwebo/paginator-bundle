<?php

declare(strict_types=1);

namespace Inwebo\PaginatorBundle\Doctrine;

use Doctrine\Common\Collections\Order;
use Doctrine\ORM\QueryBuilder;
use Inwebo\PaginatorBundle\Paginated;

abstract class AbstractQueryBuilder extends QueryBuilder
{
    public const string ALIAS = '@to_override';

    public function findAll(): static
    {
        return $this->select(static::ALIAS);
    }

    public function paginate(int $page): Paginated
    {
        return (new Paginated($this))->paginate($page);
    }

    public function count(): static
    {
        return $this->select(sprintf('COUNT(DISTINCT(%s.id)) as count', static::ALIAS));
    }

    public function selectCount(): static
    {
        return $this->addSelect(sprintf('COUNT(%s.id) as count', static::ALIAS));
    }

    public function paginated(int $pageSize = Paginated::PAGE_SIZE, bool $lastPageFirst = false): Paginated
    {
        return new Paginated($this, $pageSize, $lastPageFirst);
    }

    public function orderByCreatedAtDesc(): static
    {
        return $this->orderBy(sprintf('%s.createdAt', static::ALIAS), Order::Descending->value);
    }

    public function orderByCreatedAtAsc(): static
    {
        return $this->orderBy(sprintf('%s.createdAt', static::ALIAS), Order::Ascending->value);
    }
}
