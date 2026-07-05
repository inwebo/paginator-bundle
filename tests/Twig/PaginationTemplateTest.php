<?php

declare(strict_types=1);

namespace Inwebo\PaginatorBundle\Tests\Twig;

use Inwebo\PaginatorBundle\Model\Dto\PaginationBounds;
use Inwebo\PaginatorBundle\Tests\PaginatedStub;
use Inwebo\PaginatorBundle\Twig\PaginationExtension;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Twig\Environment;
use Twig\Loader\ArrayLoader;
use Twig\Markup;
use Twig\TwigFunction;

#[CoversClass(PaginationExtension::class)]
final class PaginationTemplateTest extends TestCase
{
    private Environment $twig;

    protected function setUp(): void
    {
        $template = (string) file_get_contents(__DIR__.'/../../templates/paginated/pages.html.twig');

        $this->twig = new Environment(
            new ArrayLoader(['@InweboPaginator/paginated/pages.html.twig' => $template]),
            ['autoescape' => false]
        );

        // pagination_page mocked: renders the title directly so we can assert its presence/absence
        $this->twig->addFunction(new TwigFunction(
            'pagination_page',
            static fn (int $page, string $content, string $title): Markup => new Markup(
                sprintf('<li data-page="%d">%s</li>', $page, htmlspecialchars($title, \ENT_QUOTES)),
                'UTF-8'
            )
        ));

        // pagination_bounds uses the real implementation — wrapped in a closure to avoid
        // Twig's extension-registration check that triggers when using [$extension, 'method'] syntax
        $extension = new PaginationExtension($this->twig);
        $this->twig->addFunction(new TwigFunction(
            'pagination_bounds',
            static fn ($paginated) => $extension->getPaginationBounds($paginated)
        ));
    }

    private function render(PaginatedStub $paginator): string
    {
        return $this->twig->render('@InweboPaginator/paginated/pages.html.twig', ['paginator' => $paginator]);
    }

    /**
     * When there is only 1 page the navigation buttons must not be rendered.
     */
    public function testSinglePageShowsNoNavigationButtons(): void
    {
        $paginator = new PaginatedStub(currentPage: 1, numResults: 5, pageSize: 30);

        $html = $this->render($paginator);

        $this->assertStringNotContainsString('Dernière page', $html, '"Dernière page" must not appear when there is only 1 page');
        $this->assertStringNotContainsString('Page suivante', $html, '"Page suivante" must not appear when there is only 1 page');
        $this->assertStringNotContainsString('Page précédente', $html, '"Page précédente" must not appear when there is only 1 page');
        $this->assertStringNotContainsString('Première page', $html, '"Première page" must not appear when there is only 1 page');
    }

    /**
     * The single page number button itself must still be rendered.
     */
    public function testSinglePageShowsPageNumberButton(): void
    {
        $paginator = new PaginatedStub(currentPage: 1, numResults: 5, pageSize: 30);

        $html = $this->render($paginator);

        $this->assertStringContainsString('data-page="1"', $html, 'Page 1 button must be rendered');
    }

    /**
     * No pagination rendered at all when there are no results.
     */
    public function testEmptyResultsShowsNothing(): void
    {
        $paginator = new PaginatedStub(currentPage: 1, numResults: 0, pageSize: 30);

        $html = trim($this->render($paginator));

        $this->assertEmpty($html, 'No pagination should be rendered when there are no results');
    }

    /**
     * @return array<string, array{int, int, int, list<string>, list<string>}>
     */
    public static function navigationButtonsProvider(): array
    {
        return [
            '1 page: no navigation buttons' => [
                1, 5, 30,
                [],
                ['Dernière page', 'Page suivante', 'Page précédente', 'Première page'],
            ],
            'first of many: last+next shown, prev+first hidden' => [
                1, 100, 10,
                ['Dernière page', 'Page suivante'],
                ['Page précédente', 'Première page'],
            ],
            'last of many: prev+first shown, last+next hidden' => [
                10, 100, 10,
                ['Page précédente', 'Première page'],
                ['Dernière page', 'Page suivante'],
            ],
            'middle of many: all four navigation buttons shown' => [
                5, 100, 10,
                ['Dernière page', 'Page suivante', 'Page précédente', 'Première page'],
                [],
            ],
        ];
    }

    /**
     * @param list<string> $expectedPresent
     * @param list<string> $expectedAbsent
     */
    #[DataProvider('navigationButtonsProvider')]
    public function testNavigationButtonsVisibility(
        int $currentPage,
        int $numResults,
        int $pageSize,
        array $expectedPresent,
        array $expectedAbsent,
    ): void {
        $paginator = new PaginatedStub($currentPage, $numResults, $pageSize);
        $html = $this->render($paginator);

        foreach ($expectedPresent as $label) {
            $this->assertStringContainsString($label, $html, sprintf('"%s" should be present', $label));
        }

        foreach ($expectedAbsent as $label) {
            $this->assertStringNotContainsString($label, $html, sprintf('"%s" should be absent', $label));
        }
    }

    /**
     * @return array<string, array{int, int, int, int, int}>
     */
    public static function boundsProvider(): array
    {
        return [
            '1 page' => [1, 5, 10, 1, 1],
            'first page of many' => [1, 300, 10, 11, 1],
            'middle of many' => [15, 300, 10, 20, 10],
            'near last page' => [28, 300, 10, 30, 19],
        ];
    }

    #[DataProvider('boundsProvider')]
    public function testPaginationBoundsRange(
        int $currentPage,
        int $numResults,
        int $pageSize,
        int $expectedStart,
        int $expectedEnd,
    ): void {
        $paginator = new PaginatedStub($currentPage, $numResults, $pageSize);
        $extension = new PaginationExtension($this->twig);
        $bounds = $extension->getPaginationBounds($paginator);

        $this->assertInstanceOf(PaginationBounds::class, $bounds);
        $this->assertSame($expectedStart, $bounds->getStart());
        $this->assertSame($expectedEnd, $bounds->getEnd());
    }
}
