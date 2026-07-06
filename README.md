# inwebo/paginator-bundle

A lightweight Symfony 7/8 bundle providing Doctrine-backed pagination with Twig helpers.

## Requirements

- PHP ≥ 8.1
- Symfony 7 or 8
- Doctrine ORM ^3.0
- Twig ^3.21

## Installation

```bash
composer require inwebo/paginator-bundle
```

The bundle registers itself automatically via Symfony Flex. No manual kernel registration needed.

## Usage

There are two ways to add pagination to a repository: the **trait approach** (recommended, no forced inheritance) and the **abstract class approach** (full opinionated stack).

---

### Approach 1 — Trait (recommended)

Use `PaginationRepositoryTrait` on any existing repository. No change to the class hierarchy is required.

```php
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Inwebo\PaginatorBundle\Doctrine\PaginationRepositoryTrait;

class PostRepository extends ServiceEntityRepository
{
    use PaginationRepositoryTrait;
}
```

Then in a controller:

```php
// Simple page-based pagination
$paginator = $repository->paginate(
    $repository->createQueryBuilder('p')->where('p.published = true'),
    page: $page,
    pageSize: 20,
);

// Reverse-chronological (newest first, requires a count resolver)
$qb = $repository->createQueryBuilder('p');
$paginator = $repository->paginated(
    $qb,
    pageSize: 20,
    lastPageFirst: true,
    countResolver: static fn (): int =>
        (int) (clone $qb)
            ->select('COUNT(DISTINCT(p.id))')
            ->getQuery()
            ->getSingleScalarResult(),
);
$paginator->paginate(null); // null → resolves to the last page automatically
```

---

### Approach 2 — Abstract classes (opinionated stack)

Extend `AbstractQueryBuilder` and `AbstractServiceEntityRepository` for a more integrated experience. The `ALIAS` constant drives all generated SQL fragments.

#### Query builder

```php
use Inwebo\PaginatorBundle\Doctrine\AbstractQueryBuilder;

class PostQueryBuilder extends AbstractQueryBuilder
{
    public const string ALIAS = 'p';

    public function __construct(\Doctrine\ORM\EntityManager $em)
    {
        parent::__construct($em);
        $this->from(Post::class, self::ALIAS);
    }

    public function published(): static
    {
        return $this->andWhere(self::ALIAS . '.publishedAt IS NOT NULL');
    }
}
```

#### Repository

```php
use Inwebo\PaginatorBundle\Doctrine\AbstractServiceEntityRepository;

/**
 * @template-extends AbstractServiceEntityRepository<Post>
 */
class PostRepository extends AbstractServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Post::class);
    }

    public function createQueryBuilder($alias, $indexBy = null): PostQueryBuilder
    {
        return (new PostQueryBuilder($this->getEntityManager()))->findAll();
    }
}
```

#### Controller

```php
// Simple pagination
$paginator = $repository->createQueryBuilder('p')
    ->published()
    ->orderByCreatedAtDesc()
    ->paginate($page);

// Reverse-chronological
$paginator = $repository->createQueryBuilder('p')
    ->published()
    ->paginated(pageSize: 20, lastPageFirst: true)
    ->paginate(null);
```

`AbstractServiceEntityRepository` includes `PaginationRepositoryTrait`, so both `.paginate()` and `.paginated()` are also available directly on the repository (not only on the query builder).

---

### Rendering in Twig

Include the built-in template, passing the `Paginated` object as `paginator`:

```twig
{% include '@InweboPaginator/paginated/pages.html.twig' with { paginator: paginator } %}
```

The template renders a `<ul aria-label="pagination">` with navigation buttons (first, previous, numbered pages, next, last). Navigation buttons are hidden when there is only one page.

### Iterating over results

`Paginated` is not itself iterable — after calling `paginate()`, iterate over `getResults()` to display the current page's items:

```php
$paginator = $repository->paginate($qb, page: $page);

foreach ($paginator->getResults() as $post) {
    echo $post->getTitle();
}
```

The same works directly in Twig, alongside the pagination navigation template:

```twig
<ul>
    {% for post in paginator.results %}
        <li>{{ post.title }}</li>
    {% endfor %}
</ul>

{% include '@InweboPaginator/paginated/pages.html.twig' with { paginator: paginator } %}
```

Use `getReverseIterator()` instead of `getResults()` to walk the current page back to front (e.g. a chat or activity log where the newest item of the page should appear first):

```twig
{% for message in paginator.reverseIterator %}
    <li>{{ message.content }}</li>
{% endfor %}
```

#### Route naming convention

The item template builds pagination URLs from the current route. It appends `_paginated` to the route name if not already present. Declare your paginated route accordingly:

```php
// config/routes/post.php
$routes->add('app_post_index_paginated', '/posts/{page}')
    ->controller(PostController::class)
    ->defaults(['page' => 1]);
```

---

### `Paginated` API

| Method | Description |
|---|---|
| `paginate(int $page): self` | Execute the query and set the current page |
| `getCurrentPage(): int` | Current page number |
| `getLastPage(): int` | Total number of pages |
| `getCountPages(): int` | Alias for `getLastPage()` |
| `hasPreviousPage(): bool` | Whether a previous page exists |
| `hasNextPage(): bool` | Whether a next page exists |
| `getPreviousPage(): int` | Previous page number (min 1) |
| `getNextPage(): int` | Next page number (max last) |
| `hasToPaginate(): bool` | Whether results exceed one page |
| `getNumResults(): int` | Total result count |
| `getResults(): \Traversable` | Current page results |
| `getReverseIterator(): \Traversable` | Iterate current page in reverse order |

### Twig functions

| Function | Description |
|---|---|
| `pagination_bounds(paginator)` | Returns a `PaginationBounds` DTO with `start`/`end` for the visible page window (max 11 pages) |
| `pagination_page(page, content, title, isActive, forceDisplay)` | Renders a single `<li>` page link |

---

## Development

```bash
# Run tests
composer phpunit

# Code style
composer php-cs-fixer

# Static analysis (PHPStan level 10)
composer phpstan
```

## Credits

This bundle extracts and generalizes pagination code originally written by [inwebo](https://github.com/inwebo) for internal projects, packaged here for reuse across applications. Claude (Anthropic) assisted with the extraction, decoupling from the original application code, tests, documentation, and CI/CD setup.
