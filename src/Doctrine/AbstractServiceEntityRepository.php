<?php

declare(strict_types=1);

namespace Inwebo\PaginatorBundle\Doctrine;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @template T of object
 *
 * @template-extends ServiceEntityRepository<T>
 *
 * @method AbstractQueryBuilder createQueryBuilder($alias, $indexBy = null)
 */
abstract class AbstractServiceEntityRepository extends ServiceEntityRepository
{
    use PaginationRepositoryTrait;
}
