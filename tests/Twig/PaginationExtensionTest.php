<?php

declare(strict_types=1);

namespace Inwebo\PaginatorBundle\Tests\Twig;

use Inwebo\PaginatorBundle\Model\Dto\PaginationBounds;
use Inwebo\PaginatorBundle\Paginated;
use Inwebo\PaginatorBundle\Tests\PaginatedStub;
use Inwebo\PaginatorBundle\Twig\PaginationExtension;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Twig\Environment;

#[CoversClass(PaginationExtension::class)]
final class PaginationExtensionTest extends TestCase
{
    private PaginationExtension $extension;

    public function setUp(): void
    {
        $this->extension = new PaginationExtension($this->createMock(Environment::class));
    }

    private function paginated(int $currentPage, int $numResults, int $pageSize = Paginated::PAGE_SIZE): Paginated
    {
        return new PaginatedStub($currentPage, $numResults, $pageSize);
    }

    /**
     * @return array<string, array{int, int, int, int, int}>
     */
    public static function boundsProvider(): array
    {
        return [
            'fewer pages than DISPLAY_PAGES shows all' => [1, 50, 10, 5, 1],
            'on first page of many' => [1, 300, 10, Paginated::DISPLAY_PAGES, 1],
            'middle of many pages' => [15, 300, 10, 15 + 5, 15 - 5],
            'near last page' => [28, 300, 10, 30, 30 - Paginated::DISPLAY_PAGES],
            'exactly at last page' => [30, 300, 10, 30, 30 - Paginated::DISPLAY_PAGES],
        ];
    }

    #[DataProvider('boundsProvider')]
    public function testGetPaginationBounds(int $currentPage, int $numResults, int $pageSize, int $expectedStart, int $expectedEnd): void
    {
        $paginated = $this->paginated($currentPage, $numResults, $pageSize);
        $bounds = $this->extension->getPaginationBounds($paginated);

        $this->assertInstanceOf(PaginationBounds::class, $bounds);
        $this->assertSame($expectedStart, $bounds->getStart());
        $this->assertSame($expectedEnd, $bounds->getEnd());
    }

    public function testGetFunctions(): void
    {
        $functions = $this->extension->getFunctions();

        $this->assertCount(2, $functions);
        $names = array_map(fn ($f) => $f->getName(), $functions);
        $this->assertContains('pagination_page', $names);
        $this->assertContains('pagination_bounds', $names);
    }
}
