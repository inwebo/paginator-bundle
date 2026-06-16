# Changelog

## [1.0.0] - Unreleased

### Added

- `Paginated` wrapping Doctrine Paginator with page navigation helpers and `lastPageFirst` support
- `AbstractQueryBuilder` extending `Doctrine\ORM\QueryBuilder` with `paginate()`, `paginated()`, `count()`, `findAll()`, `orderByCreatedAtDesc/Asc()` helpers
- `AbstractServiceEntityRepository` base class for Doctrine repositories
- `PaginationBounds` readonly DTO for computing the visible page window
- Twig extension with `pagination_page()` and `pagination_bounds()` functions
- Templates `@InweboPaginator/paginated/pages.html.twig` and `@InweboPaginator/paginated/item/page.html.twig`
- PHPStan level 10 + PHP-CS-Fixer Symfony ruleset
