<?php

declare(strict_types=1);

namespace Inwebo\PaginatorBundle\Twig;

use Inwebo\PaginatorBundle\Model\Dto\PaginationBounds;
use Inwebo\PaginatorBundle\Paginated;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use Twig\Extension\AbstractExtension;
use Twig\Markup;
use Twig\TwigFunction;

class PaginationExtension extends AbstractExtension
{
    public function __construct(private readonly Environment $environment)
    {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('pagination_page', [$this, 'getPaginationPage']),
            new TwigFunction('pagination_bounds', [$this, 'getPaginationBounds']),
        ];
    }

    /**
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function getPaginationPage(int $page, string $content, string $title, bool $isActive = false, bool $forceDisplay = false): Markup
    {
        $html = $this->environment->render('@InweboPaginator/paginated/item/page.html.twig', [
            'page' => $page,
            'content' => $content,
            'title' => $title,
            'isActive' => $isActive,
            'forceDisplay' => $forceDisplay,
        ]);

        return new Markup($html, 'UTF-8');
    }

    public function getPaginationBounds(Paginated $paginated): PaginationBounds
    {
        if ($paginated->getCountPages() < Paginated::DISPLAY_PAGES) {
            return new PaginationBounds($paginated->getLastPage(), 1);
        }

        if (
            ($paginated->getLastPage() >= $paginated->getCurrentPage())
            && ($paginated->getCurrentPage() >= $paginated->getLastPage() - Paginated::DISPLAY_PAGES)
        ) {
            return new PaginationBounds(
                $paginated->getLastPage(),
                $paginated->getLastPage() - Paginated::DISPLAY_PAGES
            );
        }

        if (
            ($paginated->getCurrentPage() >= 1)
            && ($paginated->getCurrentPage() <= Paginated::DISPLAY_PAGES)
        ) {
            return new PaginationBounds(Paginated::DISPLAY_PAGES, 1);
        }

        $index = (int) floor(Paginated::DISPLAY_PAGES / 2);

        return new PaginationBounds(
            $paginated->getCurrentPage() + $index,
            $paginated->getCurrentPage() - $index
        );
    }
}
