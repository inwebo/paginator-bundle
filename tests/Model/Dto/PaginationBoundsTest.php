<?php

declare(strict_types=1);

namespace Inwebo\PaginatorBundle\Tests\Model\Dto;

use Inwebo\PaginatorBundle\Model\Dto\PaginationBounds;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(PaginationBounds::class)]
final class PaginationBoundsTest extends TestCase
{
    public function testGetters(): void
    {
        $bounds = new PaginationBounds(10, 1);

        $this->assertSame(10, $bounds->getStart());
        $this->assertSame(1, $bounds->getEnd());
    }

    public function testIsReadonly(): void
    {
        $reflection = new \ReflectionClass(PaginationBounds::class);

        $this->assertTrue($reflection->isReadOnly());
    }
}
