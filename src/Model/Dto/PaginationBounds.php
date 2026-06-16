<?php

declare(strict_types=1);

namespace Inwebo\PaginatorBundle\Model\Dto;

readonly class PaginationBounds
{
    public function __construct(private int $start, private int $end)
    {
    }

    public function getStart(): int
    {
        return $this->start;
    }

    public function getEnd(): int
    {
        return $this->end;
    }
}
